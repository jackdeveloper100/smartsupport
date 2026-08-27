<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoMeta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class SeoController extends Controller
{
    /**
     * Update the sitemap file with enabled SEO metadata.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sitemapUpdate(): \Illuminate\Http\JsonResponse
    {
        $data = [];
        $seoRecords = SeoMeta::select('url', 'last_modified', 'change_frequency', 'priority')
            ->where('sitemap_enable', 1)
            ->get();

        if ($seoRecords->isNotEmpty()) {
            foreach ($seoRecords as $record) {
                $data[] = [
                    'url' => $record->url,
                    'last_modified' => $record->last_modified,
                    'change_frequency' => $record->change_frequency,
                    'priority' => $record->priority,
                ];
            }
        } else {
            $data[] = [
                'url' => URL::to('/'),
                'last_modified' => now()->subYear()->format('Y-m-d H:i:s'),
                'change_frequency' => 'weekly',
                'priority' => 1.0,
            ];
        }

        file_put_contents(
            public_path('sitemap.xml'),
            view('admin/seo/sitemap', compact('data'))
        );

        return response()->json([
            'status' => 1,
            'message' => 'Sitemap updated successfully.',
            'next' => 'load',
            'url' => 'admin/seo/meta',
        ]);
    }

    /**
     * Show the SEO metadata index page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        return view('admin/seo/index');
    }

    /**
     * List all SEO metadata.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse
    {
        $seoMetaList = (new SeoMeta())->Seometalist($request->all());
        return response()->json($seoMetaList);
    }

    /**
     * Show the create SEO metadata form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('admin/seo/create');
    }

    /**
     * Show the update form for a specific SEO metadata record.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $model = SeoMeta::find($request->input('id'));
        if (!$model) {
            return redirect()->route('note')->withErrors(['error' => 'No data found.']);
        }

        return view('admin/seo/update', compact('model'));
    }

    /**
     * Save a new or updated SEO metadata record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request): \Illuminate\Http\JsonResponse
    {
        $response = (new SeoMeta())->store($request->all());
        return response()->json($response);
    }

    /**
     * Delete a specific SEO metadata record.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seo_meta,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed.',
            ]);
        }

        $seoMeta = SeoMeta::find($request->input('id'));

        if (!$seoMeta) {
            return response()->json([
                'status' => 0,
                'message' => 'No data found.',
            ]);
        }

        Cache::forget('seo_meta_' . $seoMeta->url);
        $seoMeta->delete();

        return response()->json([
            'status' => 1,
            'message' => 'SEO metadata deleted successfully.',
            'next' => 'table_refresh',
        ]);
    }
}
