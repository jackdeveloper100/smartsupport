<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\DocumentType;
use App\Models\Document;

class DocumentHelper
{
    /**
     * Normalize company_allowed_documents.document_type_id values into unique integer IDs.
     *
     * @param \Illuminate\Support\Collection $documentTypeValues
     * @return \Illuminate\Support\Collection
     */
    private static function normalizeAllowedDocumentTypeIds(Collection $documentTypeValues)
    {
        return $documentTypeValues
            ->map(function ($doc) {
                if (is_string($doc)) {
                    $decoded = json_decode($doc, true);
                    return is_array($decoded) ? $decoded : [$doc];
                }

                return is_array($doc) ? $doc : [$doc];
            })
            ->flatten()
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Get allowed document type IDs for all companies or a specific company.
     *
     * @param int|null $companyId
     * @return \Illuminate\Support\Collection
     */
    private static function getAllowedDocumentTypeIdsFromTable($companyId = null)
    {
        $query = DB::table('company_allowed_documents');

        if (!is_null($companyId)) {
            $query->where('company_id', $companyId);
        }

        return self::normalizeAllowedDocumentTypeIds($query->pluck('document_type_id'));
    }

    /**
     * Get compliance percentage for a contractor using company_allowed_documents.
     *
     * @param int $contractorId
     * @param int $companyId Contractor's company ID
     * @return string Percentage string like '75 %'
     */
    public static function getComplianceStatus($contractorId)
    {
        $contractor = \App\Models\User::find($contractorId);
        if (!$contractor || !$contractor->company_id) {
            return '0 %';
        }
        $companyId = $contractor->company_id;
        
        // Parse JSON arrays from document_type_id column
        $allowedDocTypeIds = self::getAllowedDocumentTypeIdsFromTable($companyId);

        $totalDocumentTypes = DocumentType::where('is_hidden', 0)
            ->whereIn('id', $allowedDocTypeIds)
            ->count();

        $compliantDocuments = Document::join('document_type', 'document.type', '=', 'document_type.id')
            ->where('document.user_id', $contractorId)
            ->where('document.approve_status', 1)
            ->whereIn('document.type', $allowedDocTypeIds)
            ->where('document_type.is_hidden', 0)
            ->distinct('document.type')
            ->count('document.type');

        $percentage = $totalDocumentTypes > 0 ? (int) round(((float) $compliantDocuments / (float) $totalDocumentTypes) * 100) : 0;
        return ((int) max(0, min(100, $percentage))) . ' %';
    }

    public static function getComplianceNumber($userId)
    {
        $user = \App\Models\User::find($userId);
        if (!$user) return 0;
    
        $companyId = (int) $user->company_id;
        if ($companyId <= 0) return 0;
        
        // Parse JSON arrays from document_type_id column
       // $allowedDocs = self::getAllowedDocumentTypeIdsFromTable($companyId);
         $allowedDocs = self::getAllowedVisibleDocTypeIds($companyId);

        $totalRequired = (int) $allowedDocs->count();
        if ($totalRequired <= 0) return 0;
    
        // Only query if we have valid IDs to search
        if ($allowedDocs->isEmpty()) return 0;
        
        $uploaded = (int) Document::where('user_id', $userId)
            ->whereIn('type', $allowedDocs->toArray())
            ->where('approve_status', 1)
            ->count();
    
        $percentage = (int) round(((float) $uploaded / (float) $totalRequired) * 100);
        return max(0, min(100, $percentage)); // Ensure 0-100 range
    }

    /**
     * Get compliance percentage using all visible document types (fallback).
     *
     * @param int $contractorId
     * @return string Percentage string like '75 %'
     */
    public static function getGlobalCompliance($contractorId)
    {
        $totalDocumentTypes = DocumentType::where('is_hidden', 0)->count();

        $compliantDocuments = Document::join('document_type', 'document.type', '=', 'document_type.id')
            ->where('document.user_id', $contractorId)
            ->where('document.approve_status', 1)
            ->where('document_type.is_hidden', 0)
            ->distinct('document.type')
            ->count('document.type');

        $percentage = $totalDocumentTypes > 0 ? (int) round(((float) $compliantDocuments / (float) $totalDocumentTypes) * 100) : 0;

        return ((int) max(0, min(100, $percentage))) . ' %';
    }

    /**
     * Get number of required documents for company (type=1).
     *
     * @param int $companyId
     * @return int Count
     */
    public static function getTotalRequiredDocument($companyId)
    {
        $allowedIds = self::getAllowedDocumentTypeIdsFromTable($companyId);

        return DocumentType::whereIn('id', $allowedIds)
            ->where('type', 1)
            ->where('is_hidden', 0)
            ->count();
    }
    
     /**
     * Get allowed document type IDs for a given company.
     * Handles JSON array format in document_type_id column.
     *
     * @param int $companyId
     * @return \Illuminate\Support\Collection
     */
    public static function getAllowedDocTypeIds($companyId)
    {
        return self::getAllowedDocumentTypeIdsFromTable($companyId);
    }

    /**
     * Get allowed visible document type IDs for a given company.
     *
     * @param int $companyId
     * @return \Illuminate\Support\Collection
     */
    public static function getAllowedVisibleDocTypeIds($companyId)
    {
        $allowedIds = self::getAllowedDocTypeIds($companyId);

        if ($allowedIds->isEmpty()) {
            return new Collection();
        }

        return DocumentType::where('is_hidden', 0)
            ->whereIn('id', $allowedIds->toArray())
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->values();
    }

    /**
     * Get allowed document types for a given company.
     *
     * @param int $companyId
     * @return \Illuminate\Support\Collection
     */
    public static function getAllowedDocumentTypes($companyId)
    {
        $allowedIds = self::getAllowedVisibleDocTypeIds($companyId);

        return DocumentType::where('is_hidden', 0)
            ->whereIn('id', $allowedIds->toArray())
            ->get();
    }

    /**
     * Get all visible document types used in company_allowed_documents.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAdminAllowedDocumentTypes()
    {
        $allowedIds = self::getAllowedDocumentTypeIdsFromTable();

        if ($allowedIds->isEmpty()) {
            return new Collection();
        }

        return DocumentType::where('is_hidden', 0)
            ->whereIn('id', $allowedIds->toArray())
            ->orderBy('name')
            ->get();
    }

    /**
     * Get admin dashboard expired document stats from company_allowed_documents mapping.
     *
     * @return array{expiredUser:int,expiredPercentage:int}
     */
    public static function getAdminDashboardDocumentStats()
    {
        $contractors = \App\Models\User::where('type', 1)
            ->select('id', 'company_id')
            ->get();

        $allowedDocumentsByCompany = $contractors->pluck('company_id')
            ->filter()
            ->unique()
            ->mapWithKeys(function ($companyId) {
                return [$companyId => self::getAllowedVisibleDocTypeIds($companyId)];
            });

        $documentData = (int) $contractors->sum(function ($contractor) use ($allowedDocumentsByCompany) {
            return $allowedDocumentsByCompany->get($contractor->company_id, new Collection())->count();
        });

        $latestDocuments = Document::whereIn('user_id', $contractors->pluck('id'))
            ->whereHas('documentType', function ($query) {
                $query->where('is_hidden', 0);
            })
            ->orderByDesc('updated_at')
            ->get(['user_id', 'type', 'status', 'updated_at'])
            ->unique(function ($document) {
                return $document->user_id . ':' . $document->type;
            });

        $contractorCompanyMap = $contractors->pluck('company_id', 'id');

        $allowedDocuments = $latestDocuments->filter(function ($document) use ($contractorCompanyMap, $allowedDocumentsByCompany) {
            $companyId = $contractorCompanyMap->get($document->user_id);
            $allowedTypeIds = $allowedDocumentsByCompany->get($companyId, new Collection());

            return $allowedTypeIds->contains((int) $document->type);
        });

        $activeUser = (int) $allowedDocuments->where('status', 5)->count();
        $compliancePercentage = $documentData > 0 ? (int) round(($activeUser / $documentData) * 100) : 0;

        $expiredUser = (int) $allowedDocuments->where('status', 3)->count();
        $expiredPercentage = $documentData > 0 ? (int) round(($expiredUser / $documentData) * 100) : 0;

        $deactiveUser = (int) $allowedDocuments->where('status', 2)->count();
        $deactivePercentage = $documentData > 0 ? (int) round(($deactiveUser / $documentData) * 100) : 0;

        return [
            'activeUser' => $activeUser,
            'compliancePercentage' => $compliancePercentage,
            'expiredUser' => $expiredUser,
            'expiredPercentage' => $expiredPercentage,
            'deactiveUser' => $deactiveUser,
            'deactivePercentage' => $deactivePercentage,
        ];
    }

    public static function getCompanyDashboardDocumentStats($companyId)
    {
        $contractorIds = \App\Models\User::where('type', 1)
            ->where('company_id', $companyId)
            ->pluck('id');

        $allowedDocTypeIds = self::getAllowedVisibleDocTypeIds($companyId);
        $documentData = (int) ($contractorIds->count() * $allowedDocTypeIds->count());

        $latestDocuments = Document::whereIn('user_id', $contractorIds)
            ->whereHas('documentType', function ($query) {
                $query->where('is_hidden', 0);
            })
            ->whereIn('type', $allowedDocTypeIds->toArray())
            ->orderByDesc('updated_at')
            ->get(['user_id', 'type', 'status', 'updated_at'])
            ->unique(function ($document) {
                return $document->user_id . ':' . $document->type;
            });

        $activeUser      = (int) $latestDocuments->where('status', 5)->count();
        $deactiveUser    = (int) $latestDocuments->where('status', 2)->count();
        $expiredUser     = (int) $latestDocuments->where('status', 3)->count();
        $compliancePercentage = $documentData > 0 ? (int) round(($activeUser / $documentData) * 100) : 0;
        $expiringPercentage = $documentData > 0 ? (int) round(($deactiveUser / $documentData) * 100) : 0;
        $expiredPercentage  = $documentData > 0 ? (int) round(($expiredUser / $documentData) * 100) : 0;

        return [
            'activeUser'        => $activeUser,
            'deactiveUser'      => $deactiveUser,
            'expiredUser'       => $expiredUser,
            'compliancePercentage' => $compliancePercentage,
            'expiringPercentage'=> $expiringPercentage,
            'expiredPercentage' => $expiredPercentage,
        ];
    }
    
}

