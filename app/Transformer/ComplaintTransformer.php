<?php


namespace App\Transformer;


use App\Models\Complaint;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ComplaintTransformer extends TransformerAbstract
{
    public function transform(Complaint $complaint){
        $createdDate = Carbon::parse($complaint->created_at)->toIso8601String();

        $routeShowUrl = route('admin.customer_complaint.show', ['id' => $complaint->id]);
        $code = "<a href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'>". $complaint->code. "</a>";

        $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";
    }
}
