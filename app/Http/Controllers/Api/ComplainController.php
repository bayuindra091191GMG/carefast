<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\ComplaintDetailFunc;
use App\libs\Utilities;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintDetail;
use App\Models\ComplaintFinish;
use App\Models\ComplaintFinishImage;
use App\Models\ComplaintHeaderImage;
use App\Models\ComplaintReject;
use App\Models\ComplaintRejectImage;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ComplainController extends Controller
{
    /**
     * Function to Submit Attendance.
     *
     * @param Request $request
     * @return JsonResponse
     */


    public function complaintCategories(){
        $categories = ComplaintCategory::all();

        return $categories;
    }

    public function createComplaintCustomer(Request $request)
    {
        try{
            $rules = array(
                'project_id'      => 'required',
                'subject'   => 'required',
                'message'   => 'required'
            );

            $data = json_decode($request->input('complaint_model'));

            if(empty($data->project_id)){
                return response()->json("Project ID harus terisi", 400);
            }
            if(empty($data->category_id)){
                return response()->json("Category ID harus terisi", 400);
            }
            if(empty($data->subject)){
                return response()->json("Subject harus terisi", 400);
            }
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            //checking if complain more than 5 or not
            $customerComplaintCount = Complaint::where('project_id', $data->project_id)
                ->where('customer_id', $user->id)
                ->where('status_id', '!=', 12)
                ->count();
            if($customerComplaintCount >= 5){
                return response()->json("Quota complaint anda sudah mencapai maksimal", 482);
            }

            $datetimenow = Carbon::now('Asia/Jakarta');
            $project = Project::find($data->project_id);

            //generate autonumber
            $prepend = 'CMP/'. $project->code. '/'. $datetimenow->year;
            $nextNo = Utilities::GetNextTransactionNumber($prepend);
            $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);

            if(DB::table('complaints')
                ->where('code', $complainCode)
                ->exists()){
                $nextNo = Utilities::GetNextTransactionNumber($prepend);
                $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);
            }

            //create first complaint
            //get employee ID
            $employeeDB = ProjectEmployee::where('project_id', $data->project_id)
                        ->where('employee_roles_id', '>', 1)
                        ->orderBy('employee_roles_id', 'asc')
                        ->first();
            //create customer complaint
            $newComplaint = Complaint::create([
                'code'              => $complainCode,
                'category_id'       => $data->category_id,
                'project_id'        => $data->project_id,
                'customer_id'       => $customer->id,
                'customer_name'     => $customer->name,
                'subject'           => $data->subject,
                'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'          => 10,
                'employee_handler_role_id'  => empty($employeeDB) ? null : $employeeDB->employee_roles_id,
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addMinutes(6)->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => $datetimenow->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow->toDateTimeString(),
            ]);
            //$newComplaint->code = "COMP/X/".$newComplaint->id;
            //$newComplaint->save();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => $customer->id,
                'employee_id'         => null,
                'message'             => $data->message,
                'created_by'          => $user->id,
                'created_at'          => $datetimenow->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow->toDateTimeString(),
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $newComplaint->code);
//                    Log::info('imagefolder = '.$imageFolder);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintHeaderImage::create([
                        'complaint_id'  => $newComplaint->id,
                        'image'         => $imageFolder."/".$filename,
                        'created_by'    => $user->id,
                        'created_at'    => $datetimenow->toDateTimeString(),
                        'updated_by'    => $user->id,
                        'updated_at'    => $datetimenow->toDateTimeString(),
                    ]);
                    $count++;

                }
            }

            //Update auto number
            Utilities::UpdateTransactionNumber($prepend);
            $complaintheaderImage = ComplaintHeaderImage::where('complaint_id', $newComplaint->id)->first();
            $messageImage = empty($complaintheaderImage) ? null : asset('storage/complaints/'. $complaintheaderImage->image);
//            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);

            $customerComplaintDetailModel = ([
                'customer_id'       => $newComplaintDetail->customer_id,
                'customer_name'     => $newComplaintDetail->customer->name,
                'customer_avatar'    => asset('storage/customers/'. $newComplaintDetail->customer->image_path),
                'employee_id'       => null,
                'employee_name'     => "",
                'employee_avatar'    => "",
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);
            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Customer membuat complaint baru";
            $notifData = array(
                "type_id" => 301,
                "complaint_id" => $newComplaint->id,
                "complaint_subject" => $newComplaint->subject,
                "complaint_detail_model" => $this->getComplaintDetailFunc($newComplaint->id),
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', $newComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    if(!empty($user)){
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                    }
                }
            }

            return Response::json($newComplaint->id, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - createComplaintCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function createComplaintCustomerV2(Request $request)
    {
        try{
            $rules = array(
                'project_id'      => 'required',
                'subject'   => 'required',
                'message'   => 'required'
            );

            $data = json_decode($request->input('complaint_model'));

            if(empty($data->project_id)){
                return response()->json("Project ID harus terisi", 400);
            }
            if(empty($data->category_id)){
                return response()->json("Category ID harus terisi", 400);
            }
            if(empty($data->subject)){
                return response()->json("Subject harus terisi", 400);
            }
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            //checking if complain more than 5 or not
            $customerComplaintCount = Complaint::where('project_id', $data->project_id)
                ->where('customer_id', $user->id)
                ->where('status_id', '!=', 12)
                ->count();
//            if($customerComplaintCount >= 5){
//                return response()->json("Quota complaint anda sudah mencapai maksimal", 482);
//            }

            $datetimenow = Carbon::now('Asia/Jakarta');
            $project = Project::find($data->project_id);

            //generate autonumber
            $prepend = 'CMP/'. $project->code. '/'. $datetimenow->year;
            $nextNo = Utilities::GetNextTransactionNumber($prepend);
            $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);

            if(DB::table('complaints')
                ->where('code', $complainCode)
                ->exists()){
                $nextNo = Utilities::GetNextTransactionNumber($prepend);
                $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);
            }

            //create first complaint
            //get employee ID
            $employeeDB = ProjectEmployee::where('project_id', $data->project_id)
                        ->where('employee_roles_id', '>', 1)
                        ->orderBy('employee_roles_id', 'asc')
                        ->first();
            //create customer complaint
            $newComplaint = Complaint::create([
                'code'              => $complainCode,
                'category_id'       => $data->category_id,
                'project_id'        => $data->project_id,
                'customer_id'       => $customer->id,
                'customer_name'     => $customer->name,
                'subject'           => $data->subject,
                'description'       => $data->message,
                'location'           => $data->location,
                'priority'           => $data->urgency ?? "NORMAL",
                'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'          => 10,
                'employee_handler_role_id'  => empty($employeeDB) ? null : $employeeDB->employee_roles_id,
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addMinutes(1)->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => $datetimenow->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow->toDateTimeString(),
            ]);
            //$newComplaint->code = "COMP/X/".$newComplaint->id;
            //$newComplaint->save();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => $customer->id,
                'employee_id'         => null,
                'message'             => $data->message,
                'created_by'          => $user->id,
                'created_at'          => $datetimenow->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow->toDateTimeString(),
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $newComplaint->code);
//                    Log::info('imagefolder = '.$imageFolder);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintHeaderImage::create([
                        'complaint_id'  => $newComplaint->id,
                        'image'         => $imageFolder."/".$filename,
                        'created_by'    => $user->id,
                        'created_at'    => $datetimenow->toDateTimeString(),
                        'updated_by'    => $user->id,
                        'updated_at'    => $datetimenow->toDateTimeString(),
                    ]);
                    $count++;

                }
            }

            //Update auto number
            Utilities::UpdateTransactionNumber($prepend);
            $complaintheaderImage = ComplaintHeaderImage::where('complaint_id', $newComplaint->id)->first();
            $messageImage = empty($complaintheaderImage) ? null : asset('storage/complaints/'. $complaintheaderImage->image);
//            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);

            $customerComplaintDetailModel = ([
                'customer_id'       => $newComplaintDetail->customer_id,
                'customer_name'     => $newComplaintDetail->customer->name,
                'customer_avatar'   => asset('storage/customers/'. $newComplaintDetail->customer->image_path),
                'employee_id'       => null,
                'employee_name'     => "",
                'employee_avatar'   => "",
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);
            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Customer membuat complaint baru";
            $notifData = array(
                "type_id" => 301,
                "complaint_id" => $newComplaint->id,
                "complaint_subject" => $newComplaint->subject,
                "complaint_detail_model" => $this->getComplaintDetailFunc($newComplaint->id),
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', $newComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    if(!empty($user)){
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                    }
                }
            }

            //get return data
            $customerComplaint = Complaint::where('id', $newComplaint->id)->first();
            $complaintImageDBs = $customerComplaint->complaint_header_images;
            $complaintImages = collect();
            foreach ($complaintImageDBs as $complaintImageDB){
                $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                $complaintImages->push($complaintImage);
            }
            //get complaint reject
            $rejectModels = collect();
            if(count($customerComplaint->complaint_rejects) > 0){
                $messageImage = empty($customerComplaint->complaint_rejects->image) ? null : asset('storage/complaints/'. $customerComplaint->complaint_rejects->image);
                $complaintRejectModel = ([
//                        'customer_id'       => $customerComplaints->complaint_rejects->customer_id,
//                        'customer_name'     => $customerComplaints->complaint_rejects->customer->name,
//                        'customer_avatar'   => asset('storage/customers/'. $customerComplaints->complaint_rejects->customer->image_path),
//                        'employee_id'       => null,
//                        'employee_name'     => "",
//                        'employee_avatar'   => "",
//                        'date'              => Carbon::parse($customerComplaints->complaint_rejects->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                    'message'           => $customerComplaint->complaint_rejects->message,
                    'image'             => $messageImage,
                ]);
                $rejectModels->push($complaintRejectModel);
            }
            //get last employee reply
//            $lastComplaintDetail = ComplaintDetail::where('complaint_id', $complaint->id)
//                ->where('employee_id', "!=", null)
//                ->orderBy('created_at')
//                ->first();
            $lastComplaintDetailRole = "";
            if(!empty($customerComplaint->employee_handler_id)){
                $lastComplaintDetail = Employee::where('id', $customerComplaint->employee_handler_id)->first();
                $lastComplaintDetailRole = $lastComplaintDetail->employee_role->name;
            }

            //get project's location (from project_objects tables)
            $locationModels = collect();
            $locationDB = ProjectObject::where('project_id', $customerComplaint->project_id)
                ->where('status_id', 1)
                ->get();
            if(count($locationDB) > 0){
                foreach ($locationDB as $location){
                    $locationModel = $location->place_name. " - ".$location->unit_name;
                    $locationModels->push($locationModel);
                }
            }

            $customerComplaintModel = collect([
                'id'                    => $customerComplaint->id,
//                    'category_id'           => $customerComplaint->complaint_categories->description,
                'project_id'            => $customerComplaint->project_id,
                'project_name'          => $customerComplaint->project->name,
                'code'                  => $customerComplaint->code,
                'customer_id'           => $customerComplaint->customer_id,
                'employee_id'           => $customerComplaint->employee_id,
                'employee_handler_id'   => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : "0" ,
                'employee_handler_name' => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                'employee_handler_role' => $lastComplaintDetailRole,
                'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",
                'customer_name'         => $customerComplaint->customer_name,
                'subject'               => $customerComplaint->subject,
                'date'                  => Carbon::parse($customerComplaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                'status_id'             => $customerComplaint->status_id,
                'images'                => $complaintImages,
                'reject_models'         => $rejectModels,
                'locations'              => $locationModels,
                'location'              => $customerComplaint->location,
            ]);
            return Response::json($customerComplaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - createComplaintCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function replyComplaintCustomer(Request $request)
    {
        try{
//            $rules = array(
//                'project_id'      => 'required',
//                'subject'   => 'required',
//                'message'   => 'required'
//            );
//
//            $data = $request->json()->all();
//            $validator = Validator::make($data, $rules);
//            if ($validator->fails()) {
//                return response()->json($validator->messages(), 400);
//            }
            $data = json_decode($request->input('complaint_reply_model'));

            if(!($request->hasFile('image'))){
                if(empty($data->message)){
                    return response()->json("Message harus terisi", 400);
                }
            }
            if(empty($data->complaint_id)){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $data->complaint_id,
                'customer_id'         => $customer->id,
                'employee_id'         => null,
                'message'             => $data->message,
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            $complaint = Complaint::find($data->complaint_id);
            if($request->hasFile('image')){

                $imageFolder = str_replace('/','-', $complaint->code);
                $publicPath = 'storage/complaints/'. $imageFolder;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $imageFolder . '_'. $data->complaint_id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $avatar->save(public_path($publicPath ."/". $filename));

                $newComplaintDetail->image = $imageFolder."/".$filename;
                $newComplaintDetail->save();
            }

            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);
            $customerComplaintDetailModel = ([
                'customer_id'       => $newComplaintDetail->customer_id,
                'customer_name'     => $newComplaintDetail->customer->name,
                'customer_avatar'    => asset('storage/customers/'. $newComplaintDetail->customer->image_path),
                'employee_id'       => null,
                'employee_name'     => "",
                'employee_avatar'    => "",
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);

            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Customer reply complaint ".$complaint->subject;
            $data = array(
                "type_id" => 302,
                "complaint_id" => $complaint->id,
                "complaint_detail_model" => $customerComplaintDetailModel,
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $complaint->project_id)
                ->where('employee_roles_id', $complaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                }
            }

            return Response::json($customerComplaintDetailModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - replyComplaintCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function createComplaintEmployee(Request $request)
    {
        try{
            $data = json_decode($request->input('complaint_model'));

            if(empty($data->project_id)){
                return response()->json("Project ID harus terisi", 400);
            }
            if(empty($data->category_id)){
                return response()->json("Category ID harus terisi", 400);
            }
            if(empty($data->subject)){
                return response()->json("Subject harus terisi", 400);
            }
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }

//            Log::error('project_id = '.$request->input('project_id').
//                ', subject = '.$request->input('subject').
//                ', message = '.$request->input('message'));

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = Employee::find($user->employee_id);

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $project = Project::find($data->project_id);

            //generate autonumber
            $prepend = 'CMP/'. $project->code. '/'. Carbon::today('Asia/Jakarta')->year;
            $nextNo = Utilities::GetNextTransactionNumber($prepend);
            $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);

            if(DB::table('complaints')
                ->where('code', $complainCode)
                ->exists()){
                $nextNo = Utilities::GetNextTransactionNumber($prepend);
                $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);
            }

            //create first complaint

            //get employee ID
            $employeeDB = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', '>', 1)
                ->orderBy('employee_roles_id', 'asc')
                ->first();

            //create customer complaint
            $newComplaint = Complaint::create([
                'code'              => $complainCode,
                'category_id'       => $data->category_id,
                'project_id'        => $data->project_id,
                'employee_id'       => $employee->id,
                'customer_name'     => $employee->first_name." ".$employee->last_name,
                'subject'           => $data->subject,
                'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'          => 10,
                'employee_handler_role_id'  => empty($employeeDB) ? null : $employeeDB->employee_roles_id,
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addMinutes(6)->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow,
            ]);
            //$newComplaint->code = "COMP/X/".$newComplaint->id;
            //$newComplaint->save();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => null,
                'employee_id'         => $employee->id,
                'message'             => $data->message,
                'created_by'          => $employee->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $employee->id,
                'updated_at'          => $datetimenow,
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $newComplaint->code);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintHeaderImage::create([
                        'complaint_id'  => $newComplaint->id,
                        'image'         => $imageFolder."/".$filename,
                        'created_by'    => $user->id,
                        'created_at'    => $datetimenow,
                        'updated_by'    => $user->id,
                        'updated_at'    => $datetimenow,
                    ]);
                    $count++;
                }
            }

            //Update auto number
            Utilities::UpdateTransactionNumber($prepend);
            $complaintheaderImage = ComplaintHeaderImage::where('complaint_id', $newComplaint->id)->first();
            $messageImage = empty($complaintheaderImage) ? null : asset('storage/complaints/'. $complaintheaderImage->image);
//            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);

            $employeeComplaintDetailModel = ([
                'customer_id'       => null,
                'customer_name'     => "",
                'customer_avatar'    => "",
                'employee_id'       => $newComplaintDetail->employee_id,
                'employee_name'     => $newComplaintDetail->employee->first_name." ".$newComplaintDetail->employee->last_name,
                'employee_avatar'    => asset('storage/employees/'. $newComplaintDetail->employee->image_path),
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);
            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Employee membuat complaint baru";
            $notifData = array(
                "type_id" => 301,
                "complaint_id" => $newComplaint->id,
                "complaint_subject" => $newComplaint->subject,
                "complaint_detail_model" => $employeeComplaintDetailModel,
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', $newComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    if($ProjectEmployee->employee_id != $employee->id){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                    }
                }
            }

            return Response::json($newComplaint->id, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - createComplaintEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function createComplaintEmployeeV2(Request $request)
    {
        try{
            $data = json_decode($request->input('complaint_model'));

            if(empty($data->project_id)){
                return response()->json("Project ID harus terisi", 400);
            }
            if(empty($data->category_id)){
                return response()->json("Category ID harus terisi", 400);
            }
            if(empty($data->subject)){
                return response()->json("Subject harus terisi", 400);
            }
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }

//            Log::error('project_id = '.$request->input('project_id').
//                ', subject = '.$request->input('subject').
//                ', message = '.$request->input('message'));

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = Employee::find($user->employee_id);

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $project = Project::find($data->project_id);

            //generate autonumber
            $prepend = 'CMP/'. $project->code. '/'. Carbon::today('Asia/Jakarta')->year;
            $nextNo = Utilities::GetNextTransactionNumber($prepend);
            $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);

            if(DB::table('complaints')
                ->where('code', $complainCode)
                ->exists()){
                $nextNo = Utilities::GetNextTransactionNumber($prepend);
                $complainCode = Utilities::GenerateAutoNumber($prepend, $nextNo);
            }

            //create first complaint

            //get employee ID
            $employeeDB = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', '>', 1)
                ->orderBy('employee_roles_id', 'asc')
                ->first();

            //create customer complaint
            $newComplaint = Complaint::create([
                'code'              => $complainCode,
                'category_id'       => $data->category_id,
                'project_id'        => $data->project_id,
                'employee_id'       => $employee->id,
                'customer_name'     => $employee->first_name." ".$employee->last_name,
                'subject'           => $data->subject,
                'description'       => $data->message,
                'location'           => $data->location,
                'priority'           => $data->urgency ?? "NORMAL",
                'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'          => 10,
                'employee_handler_role_id'  => empty($employeeDB) ? null : $employeeDB->employee_roles_id,
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addMinutes(1)->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow,
            ]);
            //$newComplaint->code = "COMP/X/".$newComplaint->id;
            //$newComplaint->save();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => null,
                'employee_id'         => $employee->id,
                'message'             => $data->message,
                'created_by'          => $employee->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $employee->id,
                'updated_at'          => $datetimenow,
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $newComplaint->code);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintHeaderImage::create([
                        'complaint_id'  => $newComplaint->id,
                        'image'         => $imageFolder."/".$filename,
                        'created_by'    => $user->id,
                        'created_at'    => $datetimenow,
                        'updated_by'    => $user->id,
                        'updated_at'    => $datetimenow,
                    ]);
                    $count++;
                }
            }

            //Update auto number
            Utilities::UpdateTransactionNumber($prepend);
            $complaintheaderImage = ComplaintHeaderImage::where('complaint_id', $newComplaint->id)->first();
            $messageImage = empty($complaintheaderImage) ? null : asset('storage/complaints/'. $complaintheaderImage->image);
//            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);

            $employeeComplaintDetailModel = ([
                'customer_id'       => null,
                'customer_name'     => "",
                'customer_avatar'    => "",
                'employee_id'       => $newComplaintDetail->employee_id,
                'employee_name'     => $newComplaintDetail->employee->first_name." ".$newComplaintDetail->employee->last_name,
                'employee_avatar'    => asset('storage/employees/'. $newComplaintDetail->employee->image_path),
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);
            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Employee membuat complaint baru";
            $notifData = array(
                "type_id" => 301,
                "complaint_id" => $newComplaint->id,
                "complaint_subject" => $newComplaint->subject,
                "complaint_detail_model" => $employeeComplaintDetailModel,
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', $newComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    if($ProjectEmployee->employee_id != $employee->id){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                    }
                }
            }

            //get return data
            $customerComplaint = Complaint::where('id', $newComplaint->id)->first();
            $complaintImageDBs = $customerComplaint->complaint_header_images;
            $complaintImages = collect();
            foreach ($complaintImageDBs as $complaintImageDB){
                $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                $complaintImages->push($complaintImage);
            }
            //get complaint reject
            $rejectModels = collect();
            if(count($customerComplaint->complaint_rejects) > 0){
                $messageImage = empty($customerComplaint->complaint_rejects->image) ? null : asset('storage/complaints/'. $customerComplaint->complaint_rejects->image);
                $complaintRejectModel = ([
//                        'customer_id'       => $customerComplaints->complaint_rejects->customer_id,
//                        'customer_name'     => $customerComplaints->complaint_rejects->customer->name,
//                        'customer_avatar'   => asset('storage/customers/'. $customerComplaints->complaint_rejects->customer->image_path),
//                        'employee_id'       => null,
//                        'employee_name'     => "",
//                        'employee_avatar'   => "",
//                        'date'              => Carbon::parse($customerComplaints->complaint_rejects->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                    'message'           => $customerComplaint->complaint_rejects->message,
                    'image'             => $messageImage,
                ]);
                $rejectModels->push($complaintRejectModel);
            }
            //get last employee reply
//            $lastComplaintDetail = ComplaintDetail::where('complaint_id', $complaint->id)
//                ->where('employee_id', "!=", null)
//                ->orderBy('created_at')
//                ->first();
            $lastComplaintDetailRole = "";
            if(!empty($customerComplaint->employee_handler_id)){
                $lastComplaintDetail = Employee::where('id', $customerComplaint->employee_handler_id)->first();
                $lastComplaintDetailRole = $lastComplaintDetail->employee_role->name;
            }

            //get project's location (from project_objects tables)
            $locationModels = collect();
            $locationDB = ProjectObject::where('project_id', $customerComplaint->project_id)
                ->where('status_id', 1)
                ->get();
            if(count($locationDB) > 0){
                foreach ($locationDB as $location){
                    $locationModel = $location->place_name. " - ".$location->unit_name;
                    $locationModels->push($locationModel);
                }
            }
            $customerComplaintModel = collect([
                'id'                    => $customerComplaint->id,
//                    'category_id'           => $customerComplaint->complaint_categories->description,
                'project_id'            => $customerComplaint->project_id,
                'project_name'          => $customerComplaint->project->name,
                'code'                  => $customerComplaint->code,
                'customer_id'           => $customerComplaint->customer_id,
                'employee_id'           => $customerComplaint->employee_id,
                'employee_handler_id'   => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : "0" ,
                'employee_handler_name' => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                'employee_handler_role' => $lastComplaintDetailRole,
                'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",
                'customer_name'         => $customerComplaint->customer_name,
                'subject'               => $customerComplaint->subject,
                'date'                  => Carbon::parse($customerComplaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                'status_id'             => $customerComplaint->status_id,
                'images'                 => $complaintImages,
                'reject_models'         => $rejectModels,
                'locations'              => $locationModels,
                'location'              => $customerComplaint->location,
            ]);

            return Response::json($newComplaint->id, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - createComplaintEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function replyComplaintEmployee(Request $request)
    {
        try{
            $data = json_decode($request->input('complaint_reply_model'));

            if(!($request->hasFile('image'))){
                if(empty($data->message)){
                    return response()->json("Message harus terisi", 400);
                }
            }
            if(empty($data->complaint_id)){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = Employee::find($user->employee_id);

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            //create complaint detail
            $newComplaintDetail = ComplaintDetail::create([
                'complaint_id'        => $data->complaint_id,
                'customer_id'         => null,
                'employee_id'         => $employee->id,
                'message'             => $data->message,
                'created_by'          => $employee->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $employee->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            $complaint = Complaint::find($data->complaint_id);
//            $complaint->status_id = 11;
//            $complaint->employee_handler_id = $employee->id;
//            $complaint->updated_by = $user->id;
//            $complaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
//            $complaint->response_limit_date = $datetimenow;
//            $complaint->save();

            if($request->hasFile('image')){
                $imageFolder = str_replace('/','-', $complaint->code);
                $publicPath = 'storage/complaints/'. $imageFolder;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $imageFolder . '_'. $data->complaint_id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $avatar->save(public_path($publicPath ."/". $filename));

                $newComplaintDetail->image = $imageFolder."/".$filename;
                $newComplaintDetail->save();
            }

            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);
            $employeeComplaintDetailModel = ([
                'customer_id'       => null,
                'customer_name'     => "",
                'customer_avatar'    => "",
                'employee_id'       => $newComplaintDetail->employee_id,
                'employee_name'     => $newComplaintDetail->employee->first_name." ".$newComplaintDetail->employee->last_name,
                'employee_avatar'    => asset('storage/employees/'. $newComplaintDetail->employee->image_path),
                'message'           => $newComplaintDetail->message,
                'image'             => $messageImage,
                'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
            ]);

            //Send notification to
            //Customer
            $title = "ICare";
            $body = "Employee reply complaint ".$complaint->subject;
            $data = array(
                "type_id" => 302,
                "complaint_id" => $complaint->id,
                "complaint_detail_model" => $employeeComplaintDetailModel,
            );
            //Push Notification to customer App.
            if(!empty($complaint->customer_id)){
                FCMNotification::SendNotification($complaint->customer_id, 'customer', $title, $body, $data);
            }
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $complaint->project_id)
//                ->where('employee_roles_id', $complaint->employee_handler_role_id)
                ->where('employee_roles_id', '<=', $complaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    if($ProjectEmployee->employee_id != $employee->id){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                    }
                }
            }
//            Log::error('Api/ComplainController - replyComplaintEmployee log asal');

            return Response::json($employeeComplaintDetailModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - replyComplaintEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaint(Request $request){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            // get complaint, from customer ID with same project
            if($customer->id != 2){
                $projectDB = Project::where('customer_id', 'like', '%#'.$customer->id.'#%')->get();
            }
            else{
                $projectDB = Project::where('customer_id', 'like', $customer->id.'#%')->get();
            }
            $customerString = "";
            $projectArr = [];
            foreach ($projectDB as $project){
                array_push($projectArr, $project->id);
                $customerString .= $project->customer_id;
            }
            $customerArr = explode('#', $customerString);

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');
            $categoryId = $request->input('category_id');

//            Log::info('skip: '. $skip);
//            Log::info('order_status: '. $statusId);
//            Log::info('ordering_type: '. $orderingType);

//            $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            if($categoryId != 0){
//                $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            }
//            else{
//                $customerComplaints =  Complaint::where('customer_id', $customer->id);
//            }
            if($categoryId != 0){
                $customerComplaints =  Complaint::whereIn('project_id', $projectArr)->where('category_id', $categoryId);
            }
            else{
                $customerComplaints =  Complaint::whereIn('project_id', $projectArr);
            }
            if($statusId != 0) {
                $customerComplaints = $customerComplaints->where('status_id', $statusId);
            }

            $customerComplaints = $customerComplaints
                ->orderBy('date', $orderingType)
                ->skip($skip)
                ->limit(10)
                ->get();


            $complaintModels = collect();
            if($customerComplaints->count() == 0){
                if($skip == 0){
                    return Response::json("Saat ini belum Ada complaint", 482);
                }
                else{
                    return Response::json($complaintModels, 200);
                }
            }

            foreach($customerComplaints as $customerComplaint){
                $complaintImageDBs = $customerComplaint->complaint_header_images;
                $complaintImages = collect();
                foreach ($complaintImageDBs as $complaintImageDB){
                    $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                    $complaintImages->push($complaintImage);
                }
                $customerComplaintModel = collect([
                    'id'                    => $customerComplaint->id,
//                    'category_id'           => $customerComplaint->complaint_categories->description,
                    'project_id'            => $customerComplaint->project_id,
                    'project_name'          => $customerComplaint->project->name,
                    'code'                  => $customerComplaint->code,
                    'customer_id'           => $customerComplaint->customer_id,
                    'employee_id'           => $customerComplaint->employee_id,
                    'employee_handler_id'   => $customerComplaint->employee_handler_id,
                    'customer_name'         => $customerComplaint->customer_name,
                    'subject'               => $customerComplaint->subject,
                    'date'                  => Carbon::parse($customerComplaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                    'status_id'             => $customerComplaint->status_id,
                    'images'                 => $complaintImages
                ]);
                $complaintModels->push($customerComplaintModel);
            }

            return Response::json($complaintModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaintV2(Request $request){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            // get complaint, from customer ID with same project
            if($customer->id != 2){
                $projectDB = Project::where('customer_id', 'like', '%#'.$customer->id.'#%')->get();
            }
            else{
                $projectDB = Project::where('customer_id', 'like', $customer->id.'#%')->get();
            }
            $customerString = "";
            $projectArr = [];
            foreach ($projectDB as $project){
                array_push($projectArr, $project->id);
                $customerString .= $project->customer_id;
            }
            $customerArr = explode('#', $customerString);

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');
            $categoryId = intval($request->input('category_id'));
            $projectId = intval($request->input('project_id'));

            $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d 00:00:00');
            $finishDate = Carbon::parse($request->input('finish_date'))->format('Y-m-d 00:00:00');
            $finishDate2 = Carbon::parse($finishDate)->addDay();

//            $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            if($categoryId != 0){
//                $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            }
//            else{
//                $customerComplaints =  Complaint::where('customer_id', $customer->id);
//            }
            if($projectId != 0){
                $customerComplaints =  Complaint::where('project_id', $projectId)
                    ->whereBetween('created_at', [$startDate, $finishDate2]);
            }
            else{
                $customerComplaints =  Complaint::whereIn('project_id', $projectArr)
                    ->whereBetween('created_at', [$startDate, $finishDate2]);
            }
            if($categoryId != 0){
                $customerComplaints =  $customerComplaints->where('category_id', $categoryId);
            }
            if($statusId != 0) {
                if($statusId == 11){
                    $customerComplaints = $customerComplaints->whereIn('status_id', [11,9]);
                }
                else{
                    $customerComplaints = $customerComplaints->where('status_id', $statusId);
                }
            }

            $customerComplaints = $customerComplaints
                ->orderBy('date', $orderingType)
                ->skip($skip)
                ->limit(10)
                ->get();


            $complaintModels = collect();
            if($customerComplaints->count() == 0){
                if($skip == 0){
                    return Response::json("Saat ini belum Ada complaint", 482);
                }
                else{
                    return Response::json($complaintModels, 200);
                }
            }

            foreach($customerComplaints as $customerComplaint){
                $customerComplaintModel = $this->getComplaintDetailFunc($customerComplaint->id);
                $complaintModels->push($customerComplaintModel);
            }

            return Response::json($complaintModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintV2 error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaintEmployee(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $employeeLoginRoleId = $user->employee_role_id;

            $employeeDB = ProjectEmployee::where('employee_id', $employee->id)
                ->get();
            $ids = collect();
            foreach ($employeeDB as $employee){
                $ids->push($employee->project_id);
            }

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');
            $categoryId = $request->input('category_id');

            if($categoryId != 0){
                $customerComplaints =  Complaint::whereIn('project_id', $ids)
//                    ->where('employee_handler_role_id', $employeeLoginRoleId)
                    ->where('category_id', $categoryId);
            }
            else{
                $customerComplaints =  Complaint::whereIn('project_id', $ids);
//                    ->where('employee_handler_role_id', $employeeLoginRoleId);
            }
//            $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            $customerComplaints =  Complaint::whereIn('project_id', $ids);
            if($statusId != 0) {
                $customerComplaints = $customerComplaints->where('status_id', $statusId);
            }

            $customerComplaints = $customerComplaints
                ->orderBy('date', $orderingType)
                ->skip($skip)
                ->limit(10)
                ->get();

            $complaintModels = collect();
            if($customerComplaints->count() == 0){
                if($skip == 0){
                    return Response::json("Saat ini belum Ada complaint", 482);
                }
                else{
                    return Response::json($complaintModels, 200);
                }
            }

            foreach($customerComplaints as $customerComplaint){
                $complaintImageDBs = $customerComplaint->complaint_header_images;
                $complaintImages = collect();
                foreach ($complaintImageDBs as $complaintImageDB){
                    $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                    $complaintImages->push($complaintImage);
                }
                $customerComplaintModel = collect([
                    'id'                    => $customerComplaint->id,
//                    'category_id'           => $customerComplaint->complaint_categories->description,
                    'project_id'            => $customerComplaint->project_id,
                    'project_name'          => $customerComplaint->project->name,
                    'code'                  => $customerComplaint->code,
                    'customer_id'           => $customerComplaint->customer_id,
                    'employee_id'           => $customerComplaint->employee_id,
                    'employee_handler_id'   => $customerComplaint->employee_handler_id,
                    'customer_name'         => $customerComplaint->customer_name,
                    'subject'               => $customerComplaint->subject,
                    'date'                  => Carbon::parse($customerComplaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                    'status_id'             => $customerComplaint->status_id,
                    'images'                 => $complaintImages
                ]);
                $complaintModels->push($customerComplaintModel);
            }
            return Response::json($complaintModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaintEmployeeV2(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $employeeLoginRoleId = $user->employee->employee_role_id;


            $employeeDB = ProjectEmployee::where('employee_id', $employee->id)
                ->get();
            $ids = collect();
            foreach ($employeeDB as $employee){
                $ids->push($employee->project_id);
            }

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');
            $categoryId = intval($request->input('category_id'));
            $projectId = intval($request->input('project_id'));

            $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d 00:00:00');
            $finishDate = Carbon::parse($request->input('finish_date'))->format('Y-m-d 00:00:00');
            $finishDate2 = Carbon::parse($finishDate)->addDay();

            if($projectId != 0){
//                $customerComplaints =  Complaint::where('project_id', $projectId)
//                    ->where('employee_handler_role_id', $employeeLoginRoleId)
//                    ->whereBetween('created_at', [$startDate, $finishDate2]);
                $customerComplaints =  Complaint::where('project_id', $projectId)
                    ->whereBetween('created_at', [$startDate, $finishDate2]);
            }
            else{
//                $customerComplaints =  Complaint::whereIn('project_id', $ids)
//                    ->where('employee_handler_role_id', $employeeLoginRoleId)
//                    ->whereBetween('created_at', [$startDate, $finishDate2]);
                $customerComplaints =  Complaint::whereIn('project_id', $ids)
                    ->whereBetween('created_at', [$startDate, $finishDate2]);
            }
//            $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('category_id', $categoryId);
//            $customerComplaints =  Complaint::whereIn('project_id', $ids);
            if($categoryId != 0){
                $customerComplaints =  $customerComplaints->where('category_id', $categoryId);
            }
            if($statusId != 0) {
                if($statusId == 11){
                    $customerComplaints = $customerComplaints->whereIn('status_id', [11,9]);
                }
                else{
                    $customerComplaints = $customerComplaints->where('status_id', $statusId);
                }
            }

            $customerComplaints = $customerComplaints
                ->orderBy('date', $orderingType)
                ->skip($skip)
                ->limit(10)
                ->get();

            $complaintModels = collect();
            if($customerComplaints->count() == 0){
                if($skip == 0){
                    return Response::json("Saat ini belum Ada complaint", 482);
                }
                else{
                    return Response::json($complaintModels, 200);
                }
            }
            $complaintModels = collect();
            if($customerComplaints->count() == 0){
                if($skip == 0){
                    return Response::json("Saat ini belum Ada complaint", 482);
                }
                else{
                    return Response::json($complaintModels, 200);
                }
            }

            foreach($customerComplaints as $customerComplaint){
                $customerComplaintModel = $this->getComplaintDetailFunc($customerComplaint->id);
                $complaintModels->push($customerComplaintModel);
            }
            return Response::json($complaintModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintEmployeeV2 error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getProjectLocations(Request $request){
        try{
            $user = auth('customer')->user();

            if(empty($request->input('project_id'))){
                return response()->json("Bad Request", 400);
            }

            $locationModels = collect();
            $locationDB = ProjectObject::where('project_id', $request->input('project_id'))
                ->where('status_id', 1)
                ->get();
            if(count($locationDB) > 0){
                foreach ($locationDB as $location){
//                    $locationModel = $location->place_name. " - ".$location->unit_name;
//                    $locationModels->push($locationModel);

                    $foundPlace = $locationModels->where('place_name', $location->place_name)->first();
                    if(empty($foundPlace)){
                        $objects = collect();
                        foreach ($locationDB->where('place_id', $location->place_id) as $locationObject){
                            $objectModel = $locationObject->unit_name;
                            $objects->push($objectModel);
                        }

                        $locationModel = collect([
                            'place_name' => $location->place_name,
                            'objects'   => $objects
                        ]);
                        $locationModels->push($locationModel);
                    }
                }
            }

            return Response::json($locationModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getProjectLocations error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getProjectListCustomer(){
        try{
            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            $projectModels = collect();
            $projects = Project::where('customer_id', 'like', '%'.$customer->id."%")->get();
            foreach ($projects as $project){
                $projectDetailModel = collect([
                    'id'            => $project->id,
                    'name'          => $project->name,
                    'image'          => $project->image_path == null ? asset('storage/projects/default.jpg') : asset('storage/projects/'.$project->image_path),
                    'lat'          => $project->latitude,
                    'lng'          => $project->longitude,
                    'address'          => $project->address,

                ]);
                $projectModels->push($projectDetailModel);
            }

            return Response::json($projectModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getProjectListCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getProjectListEmployee(){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $projectModels = collect();
            $projectLists = ProjectEmployee::where('employee_id', $employee->id)
                ->get();
            foreach ($projectLists as $projectList){
                $project = Project::find($projectList->project_id);
                $projectDetailModel = collect([
                    'id'            => $project->id,
                    'name'          => $project->name,
                    'image'          => $project->image_path == null ? asset('storage/projects/default.jpg') : asset('storage/projects/'.$project->image_path),
                    'lat'          => $project->latitude,
                    'lng'          => $project->longitude,
                    'address'          => $project->address,
                ]);
                $projectModels->push($projectDetailModel);
            }

            return Response::json($projectModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getProjectListEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintCount(Request $request){
        try{
            if(empty($request->input('project_id'))){
                return response()->json("Bad Request", 400);
            }

            $projectId = $request->input('project_id');
            $categoryId = $request->input('category_id');
            if($categoryId == null){
                $pendingCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 10)
                    ->where('project_id', $projectId)
                    ->count();
                $progressCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 11)
                    ->where('project_id', $projectId)
                    ->count();
                $rejectCount = DB::table('complaints')
                    ->select('id')
                    ->Where('status_id', 9)
                    ->where('project_id', $projectId)
                    ->count();
                $doneCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 8)
                    ->where('project_id', $projectId)
                    ->count();
                $closeCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 12)
                    ->where('project_id', $projectId)
                    ->count();
            }
            else if($categoryId == 0){
                $pendingCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 10)
                    ->where('project_id', $projectId)
                    ->count();
                $progressCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 11)
                    ->where('project_id', $projectId)
                    ->count();
                $rejectCount = DB::table('complaints')
                    ->select('id')
                    ->Where('status_id', 9)
                    ->where('project_id', $projectId)
                    ->count();
                $doneCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 8)
                    ->where('project_id', $projectId)
                    ->count();
                $closeCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 12)
                    ->where('project_id', $projectId)
                    ->count();
            }
            else{
                $pendingCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 10)
                    ->where('project_id', $projectId)
                    ->where('category_id', $categoryId)
                    ->count();
                $progressCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 11)
                    ->where('project_id', $projectId)
                    ->where('category_id', $categoryId)
                    ->count();
                $rejectCount = DB::table('complaints')
                    ->select('id')
                    ->Where('status_id', 9)
                    ->where('project_id', $projectId)
                    ->where('category_id', $categoryId)
                    ->count();
                $doneCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 8)
                    ->where('project_id', $projectId)
                    ->where('category_id', $categoryId)
                    ->count();
                $closeCount = DB::table('complaints')
                    ->select('id')
                    ->where('status_id', 12)
                    ->where('project_id', $projectId)
                    ->where('category_id', $categoryId)
                    ->count();
            }

            $returnModel = collect([
                'pending_count'         => $pendingCount,
                'progress_count'        => $progressCount + $rejectCount,
                'done_count'            => $doneCount,
                'close_count'           => $closeCount,
            ]);


            return Response::json($returnModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintCount error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintHeaderV2(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }

            $complaint =  Complaint::find($request->input('complaint_id'));

            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }

            $complaintImageDBs = $complaint->complaint_header_images;
            $complaintImages = collect();
            foreach ($complaintImageDBs as $complaintImageDB){
                $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                $complaintImages->push($complaintImage);
            }
            //get complaint reject
            $rejectModels = collect();
            if(count($complaint->complaint_rejects) > 0){
                $messageImage = empty($complaint->complaint_rejects->image) ? null : asset('storage/complaints/'. $complaint->complaint_rejects->image);
                $complaintRejectModel = ([
                    'customer_id'       => $complaint->complaint_rejects->customer_id,
                    'customer_name'     => $complaint->complaint_rejects->customer->name,
                    'customer_avatar'   => asset('storage/customers/'. $complaint->complaint_rejects->customer->image_path),
                    'employee_id'       => null,
                    'employee_name'     => "",
                    'employee_avatar'   => "",
                    'message'           => $complaint->complaint_rejects->message,
                    'image'             => $messageImage,
                    'date'              => Carbon::parse($complaint->complaint_rejects->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                ]);
                $rejectModels->push($complaintRejectModel);
            }

            //get project's location (from project_objects tables)
            $locationModels = collect();
            $locationDB = ProjectObject::where('project_id', $complaint->project_id)
                ->where('status_id', 1)
                ->get();
            if(count($locationDB) > 0){
                foreach ($locationDB as $location){
                    $locationModel = $location->place_name. " - ".$location->unit_name;
                    $locationModels->push($locationModel);
                }
            }
            //get last employee reply
//            $lastComplaintDetail = ComplaintDetail::where('complaint_id', $complaint->id)
//                ->where('employee_id', "!=", null)
//                ->orderBy('created_at')
//                ->first();
            $lastComplaintDetailRole = "";
            if(!empty($complaint->employee_handler_id)){
                $lastComplaintDetail = Employee::where('id', $complaint->employee_handler_id)->first();
                $lastComplaintDetailRole = $lastComplaintDetail->employee_role->name;
            }

            $customerComplaintModel = collect([
                'id'                    => $complaint->id,
//                'category_id'           => $complaint->complaint_categories->description,
                'project_id'            => $complaint->project_id,
                'project_name'          => $complaint->project->name,
                'code'                  => $complaint->code,
                'customer_id'           => $complaint->customer_id,
                'employee_id'           => $complaint->employee_id,
                'employee_handler_id'   => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : "0" ,
                'employee_handler_name' => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                'employee_handler_role' => $lastComplaintDetailRole,
                'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",
                'customer_name'         => $complaint->customer_name,
                'subject'               => $complaint->subject,
                'date'                  => Carbon::parse($complaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                'status_id'             => $complaint->status_id,
                'images'                => $complaintImages,
                'reject_models'         => $rejectModels,
                'locations'              => $locationModels,
                'is_rated'              => !empty($complaint->score) ? 1 : 0,
                'rating'                => $complaint->score,
                'rating_message'        => $complaint->score_message,
                'urgency'              => $complaint->priority,

                'category_id'           => $complaint->category_id,
                'category_name'         => $complaint->complaint_categories->description,
            ]);

            return Response::json($customerComplaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintHeaderV2 error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintHeader(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }

            $complaint =  Complaint::find($request->input('complaint_id'));

            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }

            $complaintImageDBs = $complaint->complaint_header_images;
            $complaintImages = collect();
            foreach ($complaintImageDBs as $complaintImageDB){
                $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                $complaintImages->push($complaintImage);
            }
            $customerComplaintModel = collect([
                'id'                    => $complaint->id,
//                'category_id'           => $complaint->complaint_categories->description,
                'project_id'            => $complaint->project_id,
                'project_name'          => $complaint->project->name,
                'code'                  => $complaint->code,
                'customer_id'           => $complaint->customer_id,
                'employee_id'           => $complaint->employee_id,
                'employee_handler_id'   => $complaint->employee_handler_id,
                'customer_name'         => $complaint->customer_name,
                'subject'               => $complaint->subject,
                'date'                  => Carbon::parse($complaint->date, 'Asia/Jakarta')->format('d M Y H:i:s'),
                'status_id'             => $complaint->status_id,
                'images'                 => $complaintImages
            ]);

            return Response::json($customerComplaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintHeader error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintDetails(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }
            $skip = intval($request->input('skip'));

            $complaintDetails =  ComplaintDetail::where('complaint_id', $request->input('complaint_id'));
            $complaintDetailModels = collect();

            $complaintDetails = $complaintDetails
                ->orderBy('created_at', 'desc')
                ->skip($skip)
                ->limit(10)
                ->get();

            if($complaintDetails->count() == 0){
                return Response::json($complaintDetailModels, 200);
            }
            else{
                foreach($complaintDetails as $customerComplaintDetail){
                    if(empty($customerComplaintDetail->customer_id)){
                        $messageImage = empty($customerComplaintDetail->image) ? null : asset('storage/complaints/'. $customerComplaintDetail->image);
                        $customerComplaintDetailModel = ([
                            'customer_id'       => null,
                            'customer_name'     => "",
                            'customer_avatar'   => "",
                            'employee_id'       => $customerComplaintDetail->employee_id,
                            'employee_name'     => $customerComplaintDetail->employee->first_name." ".$customerComplaintDetail->employee->last_name,
                            'employee_avatar'   => asset('storage/employees/'. $customerComplaintDetail->employee->image_path),
                            'message'           => $customerComplaintDetail->message,
                            'image'             => $messageImage,
                            'date'              => Carbon::parse($customerComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                        ]);
                    }
                    else{
                            $messageImage = empty($customerComplaintDetail->image) ? null : asset('storage/complaints/'. $customerComplaintDetail->image);
                            $customerComplaintDetailModel = ([
                                'customer_id'       => $customerComplaintDetail->customer_id,
                                'customer_name'     => $customerComplaintDetail->customer->name,
                                'customer_avatar'   => asset('storage/customers/'. $customerComplaintDetail->customer->image_path),
                                'employee_id'       => null,
                                'employee_name'     => "",
                                'employee_avatar'   => "",
                                'message'           => $customerComplaintDetail->message,
                                'image'             => $messageImage,
                                'date'              => Carbon::parse($customerComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                            ]);
                    }
                    $complaintDetailModels->push($customerComplaintDetailModel);
                }
            }

            return Response::json($complaintDetailModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetails error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /*
     * function to get complaint chat data (previously using getComplaintDetails function)
     *
     * */
    public function getComplaintChats(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }
            $skip = intval($request->input('skip'));

            $complaintDetails =  ComplaintDetail::where('complaint_id', $request->input('complaint_id'));
            $complaintDetailModels = collect();

            $complaintDetails = $complaintDetails
                ->orderBy('created_at', 'desc')
                ->skip($skip)
                ->limit(10)
                ->get();

            if($complaintDetails->count() == 0){
                return Response::json($complaintDetailModels, 200);
            }
            else{
                foreach($complaintDetails as $customerComplaintDetail){
                    if(empty($customerComplaintDetail->customer_id)){
                        $messageImage = empty($customerComplaintDetail->image) ? null : asset('storage/complaints/'. $customerComplaintDetail->image);
                        $customerComplaintDetailModel = ([
                            'customer_id'       => null,
                            'customer_name'     => "",
                            'customer_avatar'   => "",
                            'employee_id'       => $customerComplaintDetail->employee_id,
                            'employee_name'     => $customerComplaintDetail->employee->first_name." ".$customerComplaintDetail->employee->last_name,
                            'employee_avatar'   => asset('storage/employees/'. $customerComplaintDetail->employee->image_path),
                            'message'           => $customerComplaintDetail->message,
                            'image'             => $messageImage,
                            'date'              => Carbon::parse($customerComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                        ]);
                    }
                    else{
                            $messageImage = empty($customerComplaintDetail->image) ? null : asset('storage/complaints/'. $customerComplaintDetail->image);
                            $customerComplaintDetailModel = ([
                                'customer_id'       => $customerComplaintDetail->customer_id,
                                'customer_name'     => $customerComplaintDetail->customer->name,
                                'customer_avatar'   => asset('storage/customers/'. $customerComplaintDetail->customer->image_path),
                                'employee_id'       => null,
                                'employee_name'     => "",
                                'employee_avatar'   => "",
                                'message'           => $customerComplaintDetail->message,
                                'image'             => $messageImage,
                                'date'              => Carbon::parse($customerComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                            ]);
                    }
                    $complaintDetailModels->push($customerComplaintDetailModel);
                }
            }

            return Response::json($complaintDetailModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintChats error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /*
     * function to get complaint detail
     *
     * */
    public function getComplaintDetail(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }

            $complaintModel = $this->getComplaintDetailFunc($request->input('complaint_id'));

            return Response::json($complaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function processComplaint(Request $request){
        try{
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = Employee::find($user->employee_id);

            $complaint =  Complaint::find($request->input('complaint_id'));
            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
//            if(!empty($complaint->employee_handler_id)){
//                if($user->employee_id != $complaint->employee_handler_id){
//                    return Response::json("Complaint sudah di proses", 483);
//                }
////                if($employee->employee_role_id > $complaint->employee_handler_id){
////                    return Response::json("Anda tidak dapat menyelesaikan complaint", 482);
////                }
//            }

            if($complaint->status_id == 11){
                return Response::json("Complaint sudah di proses", 482);
            }

//            if($employee->id != $employeeComplaint->employee_id){
//                return Response::json("Anda tidak dapat menyelesaikan complaint", 482);
//            }

//            $employee_handler_id_history = $complaint->employee_handler_id_history."#".$employee->id;
//            $complaint->employee_handler_id_history = $employee_handler_id_history;

            $complaint->employee_handler_id = $employee->id;
            $complaint->status_id = 11;
            $complaint->updated_by = $user->id;
            $complaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $complaint->save();

            //Send notification to
            //Customer
//            $messageImage = empty($complaint->image) ? null : asset('storage/complaints/'. $complaint->image);
//            $employeeComplaintDetailModel = ([
//                'customer_id'       => null,
//                'customer_name'     => "",
//                'customer_avatar'    => "",
//                'employee_id'       => $employee->id,
//                'employee_name'     => $employee->first_name." ".$employee->last_name,
//                'employee_avatar'    => asset('storage/employees/'. $employee->image_path),
//                'message'           => $complaint->message,
//                'image'             => $messageImage,
//                'date'              => Carbon::parse($complaint->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
//            ]);
            $title = "ICare";
            $body = "Employee processing complaint ".$complaint->subject;
            $data = array(
                "type_id" => 304,
                "complaint_id" => $complaint->id,
                "complaint_detail_model" => $this->getComplaintDetailFunc($complaint->id),
            );
            //Push Notification to customer App.
            if(!empty($complaint->customer_id)){
                FCMNotification::SendNotification($complaint->customer_id, 'customer', $title, $body, $data);
            }
            return Response::json("Berhasil memproses complaint ini", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - doneComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function rejectComplaint(Request $request){
        try{
            $data = json_decode($request->input('complaint_reject_model'));
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }
            if(empty($data->complaint_id)){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            //ubah header menjadi on process lagi
            $complaint =  Complaint::find($data->complaint_id);
            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            if($customer->id != $complaint->customer_id){
                return Response::json("Anda tidak dapat reject complaint ini", 482);
            }
            $complaint->status_id = 9;
            $complaint->updated_by = $user->id;
            $complaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $complaint->save();

            $datetimenow = Carbon::now('Asia/Jakarta');

            //create complaint finish
            $newComplaintDetail = ComplaintReject::create([
                'complaint_id'        => $data->complaint_id,
                'customer_id'         => $customer->id,
                'employee_id'         => null,
                'message'             => $data->message,
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $complaint->code);
//                    Log::info('imagefolder = '.$imageFolder);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintRejectImage::create([
                        'complaint_id'  => $complaint->id,
                        'complaint_reject_id'  => $newComplaintDetail->id,
                        'image'         => $imageFolder."/".$filename,
                        'created_by'    => $user->id,
                        'created_at'    => $datetimenow->toDateTimeString(),
                        'updated_by'    => $user->id,
                        'updated_at'    => $datetimenow->toDateTimeString(),
                    ]);
                    $count++;

                }
            }

            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Customer reject complaint ".$complaint->subject;
            $data = array(
                "type_id" => 303,
                "complaint_id" => $complaint->id,
                "complaint_detail_model" => $this->getComplaintDetailFunc($complaint->id),
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $complaint->project_id)
                ->where('employee_roles_id', '<=', $complaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                }
            }

            return Response::json("Berhasil reject complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - rejectComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function closeComplaint(Request $request){
        try{
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $customerComplaint =  Complaint::find($request->input('complaint_id'));
            if(empty($customerComplaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            if($customer->id != $customerComplaint->customer_id){
                return Response::json("Anda tidak dapat menutup complaint ini", 482);
            }
            $customerComplaint->status_id = 12;
            $customerComplaint->updated_by = $user->id;
            $customerComplaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $customerComplaint->save();

            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Customer Close complaint ".$customerComplaint->subject;
            $data = array(
                "type_id" => 306,
                "complaint_id" => $customerComplaint->id,
                "complaint_detail_model" => $this->getComplaintDetailFunc($customerComplaint->id),
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $customerComplaint->project_id)
                ->where('employee_roles_id', '<=', $customerComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                }
            }

            return Response::json("Berhasil menutup complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - closeComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function closeComplaintEmployee(Request $request){
        try{
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $employeeComplaint =  Complaint::find($request->input('complaint_id'));
            if(empty($employeeComplaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            if($employee->id != $employeeComplaint->employee_id){
                return Response::json("Anda tidak dapat menutup complaint", 482);
            }
            $employeeComplaint->status_id = 12;
            $employeeComplaint->updated_by = $user->id;
            $employeeComplaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $employeeComplaint->save();

            //Send notification to
            //Employee
            $title = "ICare";
            $body = "Employee Close complaint ".$employeeComplaint->subject;
            $data = array(
                "type_id" => 306,
                "complaint_id" => $employeeComplaint->id,
                "complaint_detail_model" => $this->getComplaintDetailFunc($employeeComplaint->id),
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $employeeComplaint->project_id)
                ->where('employee_roles_id', $employeeComplaint->employee_handler_role_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                }
            }

            return Response::json("Berhasil menutup complaint ini", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - closeComplaintEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function finishComplaint(Request $request){
        try{
            $data = json_decode($request->input('complaint_finish_model'));
            if(empty($data->message)){
                return response()->json("Message harus terisi", 400);
            }
            if(empty($data->complaint_id)){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = Employee::find($user->employee_id);

            $complaint =  Complaint::where('id', $data->complaint_id)->first();
            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
//            if($employee->id != $employeeComplaint->employee_id){
//                return Response::json("Anda tidak dapat menyelesaikan complaint", 482);
//            }
            $complaint->status_id = 8;
            $complaint->updated_by = $user->id;
            $complaint->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $complaint->save();

            $datetimenow = Carbon::now('Asia/Jakarta');

            //create complaint reject
            $newComplaintDetail = ComplaintFinish::create([
                'complaint_id'        => $data->complaint_id,
                'customer_id'         => null,
                'employee_id'         => $employee->id,
                'message'             => $data->message,
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            if($request->hasFile('images')){
                $count = 1;
                foreach($request->file('images') as $exampleImage){
                    $imageFolder = str_replace('/','-', $complaint->code);
//                    Log::info('imagefolder = '.$imageFolder);
                    $publicPath = 'storage/complaints/'. $imageFolder;
                    if(!File::isDirectory($publicPath)){
                        File::makeDirectory(public_path($publicPath), 0777, true, true);
                    }

                    $image = $exampleImage;
                    $avatar = Image::make($exampleImage);
                    $extension = $image->extension();
                    $filename = $imageFolder . '_'. $newComplaintDetail->id . '_' . $count . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path($publicPath ."/". $filename));

                    $imageComplaintHeader = ComplaintFinishImage::create([
                        'complaint_id'          => $complaint->id,
                        'complaint_finish_id'   => $newComplaintDetail->id,
                        'image'                 => $imageFolder."/".$filename,
                        'created_by'            => $user->id,
                        'created_at'            => $datetimenow->toDateTimeString(),
                        'updated_by'            => $user->id,
                        'updated_at'            => $datetimenow->toDateTimeString(),
                    ]);
                    $count++;
                }
            }

            //Send notification to
            //Customer
//            $messageImage = empty($complaint->image) ? null : asset('storage/complaints/'. $complaint->image);
//
//            $employeeComplaintDetailModel = ([
//                'customer_id'       => null,
//                'customer_name'     => "",
//                'customer_avatar'    => "",
//                'employee_id'       => $employee->id,
//                'employee_name'     => $employee->first_name." ".$employee->last_name,
//                'employee_avatar'    => asset('storage/employees/'. $employee->image_path),
//                'message'           => $complaint->message,
//                'image'             => $messageImage,
//                'date'              => Carbon::parse($complaint->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
//            ]);
            $title = "ICare";
            $body = "Employee finish processing complaint ".$complaint->subject;
            $data = array(
                "type_id" => 305,
                "complaint_id" => $complaint->id,
                "complaint_detail_model" => $this->getComplaintDetailFunc($complaint->id),
            );
            //Push Notification to customer App.
            if(!empty($complaint->customer_id)){
                FCMNotification::SendNotification($complaint->customer_id, 'customer', $title, $body, $data);
            }

            return Response::json("Berhasil menyelesaikan complaint ini", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - finishComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function scoringComplaint(Request $request){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $complaintId = intval($request->input('complaint_id'));
            $message = $request->input('message');
            $score = intval($request->input('rating'));

            $complaint =  Complaint::where('id', $complaintId)->first();
            if(empty($complaint)){
                return Response::json("Complaint Tidak ditemukan", 482);
            }
            $complaint->score = $score;
            $complaint->score_message = $message;
            $complaint->save();

            return Response::json("Berhasil menilai complaint ini", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - scoringComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintDetailFunc($complaint_id){
        try{
            return ComplaintDetailFunc::getComplaintDetailFunc($complaint_id);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetailFunc error EX: '. $ex);
            return null;
        }
    }
}
