<?php 

namespace App\Models;

use App\Helpers\Pagination;
use App\Helpers\General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailTemplate extends Model{

    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_template';

      /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['key', 'title','subject', 'body','params'];

     /**
     * Retrieves paginated list of email_template for admin with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
    public function listAdmin(array $postData): array
    {
        $query = DB::table($this->table);

        // Apply search filter if search text is provided and is more than 2 characters long
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where('title', 'like', '%' . $searchText . '%');
        }

        // Retrieve paginated result using custom Pagination helper
        $result = (new Pagination())->getDataTable($query, $postData);
        $sessionUser = auth()->user();

        // Append action links based on permissions
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->action = $this->generateActionLinks($row, $sessionUser);
        }

        return $result;
    }

    /**
     * Generates action links based on user permissions for each row.
     *
     * @param object $row The row data.
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $sessionUser The authenticated user.
     * @return string The generated HTML action links.
     */
    protected function generateActionLinks(object $row, $sessionUser): string
    {
        $actionLinks = '';
        // Add update link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('admin/email-template/update')) {
            $actionLinks .= sprintf(
                '<a href="admin/email-template/update?id=%d" class="text-body pjax act-btns tool-btn me-2" ><i class="bi bi-pencil-square"></i><span class="tooltip-text">Update</span></a>',
                $row->id
            );
        }

        // Add view link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('email-template/')) {
            $actionLinks .= sprintf(
                '<a target="_blank" href="email_template/%s" class="text-body tool-btn me-2" ><i class="fa fa-eyeme-2"><span class="tooltip-text">View</span></i></a>&nbsp;',
                htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8')
            );
        }

        return $actionLinks;
    }


    /**
     * Stores or updates a page record based on provided data.
     *
     * @param array $postData The data for creating or updating a page.
     * @return array The status and message of the operation.
     */
    public function store(array $postData): array
    {   
        $general = new General();
        
        $rules = [
          'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => ['required', 'string', function ($attribute, $value, $fail) {
            // Strip tags and decode HTML entities
            $textOnly = trim(strip_tags($value));
            if (empty($textOnly)) {
                $fail('The ' . $attribute . ' field cannot be blank.');
            }
        }],
        ];  

        $validator = Validator::make($postData, $rules);


        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
// dd($postData);
        $model = self::find($postData['id']);

        if (!$model) {
            return [
                'status' => 0,
                'message' => 'Page not found.',
            ];
        }

        $model->title = $postData['title'];
        $model->subject = $postData['subject'];
        $model->body = $postData['body'];
        // $model->params = null;
        $model->save();

        return [
            'status' => 1,
            'message' => 'Email Template saved successfully' ?? 'Email Template Updated Successfully',
            'next' => 'load',
            'url' => 'admin/email-template',
        ];
    }
    
    public function getEmailTemplate($template,$data){
        $template = $this->where('key',$template)->first();
        if(!$template){
            return '';
        }
        $body = $template->body;
        foreach($data as $key => $value){
            $body = str_replace('{{'.$key.'}}',$value,$body);
        }
        return ['subject'=>$template->subject,'body'=>$body];

    }

}
?>