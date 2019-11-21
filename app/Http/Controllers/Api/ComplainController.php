<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintDetail;
use App\Models\ComplaintHeaderImage;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectEmployee;
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
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addHours(6)->toDateTimeString(),
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
                "complaint_detail_model" => $customerComplaintDetailModel,
            );
            //Push Notification to employee App.
            $ProjectEmployees = ProjectEmployee::where('project_id', $data->project_id)
                ->where('employee_roles_id', $employeeDB->employee_roles_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                }
            }

            return Response::json($newComplaint->id, 200);
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

            if($request->hasFile('image')){
                $complaint = Complaint::find($data->complaint_id);

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

            $complaint = Complaint::find($data->complaint_id);
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
            $employee = $user->employee;

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
                'response_limit_date'  => Carbon::now('Asia/Jakarta')->addHours(6)->toDateTimeString(),
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
                ->where('employee_roles_id', $employeeDB->employee_roles_id)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                }
            }

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
            $employee = $user->employee;

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
            $complaint->status_id = 11;
            $complaint->response_limit_date = $datetimenow;
            $complaint->save();

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

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');

//            Log::info('skip: '. $skip);
//            Log::info('order_status: '. $statusId);
//            Log::info('ordering_type: '. $orderingType);

            $customerComplaints =  Complaint::where('customer_id', $customer->id);
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
    public function getComplaintEmployee(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $employeeDB = ProjectEmployee::where('employee_id', $employee->id)
                ->first();

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('complaint_status'));
            $orderingType = $request->input('ordering_type');

            $customerComplaints =  Complaint::where('project_id', $employeeDB->project_id);
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
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintDetail(Request $request){
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
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
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
            $customerComplaint->save();

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
            $employeeComplaint->save();

            return Response::json("Berhasil menutup complaint ini", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - closeComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}
