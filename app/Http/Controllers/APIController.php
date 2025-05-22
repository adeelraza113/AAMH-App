<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;


//models
use App\Models\User;
use App\Models\Feedbacks;
use App\Models\MembershipCards;
use App\Models\Departments;
use App\Models\Doctors;
use App\Models\Appointments;
use App\Models\LabTestBooking;
use App\Models\LabTestBookingDetail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\HealthTip;
use App\Models\LabTestReport;
use App\Models\UserPayment;
use App\Models\Slider;
use App\Models\BloodSugar;
use App\Models\BloodPressure;
use App\Models\BodyTemperature;
use App\Models\BloodOxygen;
use App\Models\Hemoglobin;
use App\Models\UserWeight;


class APIController extends Controller
{
  public function login(Request $request)
{
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid login details'
        ], 401);
    }

    try {
        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'name' => $user->name,
            'mr_number' => $user->mr_number,
            'profile_image' => $user->profile_image ? asset('storage/uploads/' . $user->profile_image) : null,
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


   public function register(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'mr_number' => 'nullable|string|unique:users,mr_number',
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'password' => 'required|string|min:6',
            'profile_image' => 'nullable|string', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ]);
        }

        $fileName = "";
        if (isset($request->profile_image) && $request->profile_image) {
            $file = base64_decode($request->profile_image);
            $fileName = time() . '.png';
            Storage::disk('public')->put('uploads/' . $fileName, $file);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_image' => $fileName,
            'mr_number' => $request->mr_number ?? null, // nullable
            'password' => bcrypt($request->password),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully!',
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}



    public function test(Request $request){
        

        return response()->json([
            'status' => 'success',
            'access_token' => 'test api',
            'token_type' => 'Bearer',
        ]);
        
    }

    // feedback api
    public function postFeedback(Request $request){
         
        try{
            $feedback = Feedbacks::create([
                'patient_name' => $request->patient_name,
                'phone' => $request->phone,
                'consultation_date' => $request->consultation_date,
                'type' => $request->type,
                'doctor_name' => $request->doctor_name,
                'service_area' => $request->service_area,
                'age_group' => $request->age_group,
                'gender' => $request->gender,
                'visit_purpose' => $request->visit_purpose,
                'treatment_outcome' => $request->treatment_outcome,
                'additional_comments' => $request->additional_comments,
                'follow_up_permission' => $request->follow_up_permission == 'true' ? true : false,
                'overall_satisfaction' => $request->overall_satisfaction,
                'overall_satisfaction' => $request->overall_satisfaction,
                'consultation_rating' => $request->consultation_rating,
                'quality_of_facilities' => $request->quality_of_facilities,
                'staff_behavior' => $request->staff_behavior,
                'empathy_and_respect' => $request->empathy_and_respect
            ]);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Feedback posted successfully!',
            ], 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        } 
    }

    public function allFeedback(Request $request){
        $feedback = Feedbacks::latest()->get();
        return response()->json([
            'status' => 'success',
            'feedbacks' => $feedback,
        ]);
    }

    public function getFeedbackRatings(Request $request){
        $feedback_rating = array(
            'overall_satisfaction' => Feedbacks::where('status', 'Active')->avg('overall_satisfaction'),
            'consultation_rating' => Feedbacks::where('status', 'Active')->avg('consultation_rating'),
            'quality_of_facilities' => Feedbacks::where('status', 'Active')->avg('quality_of_facilities'),
            'staff_behavior' => Feedbacks::where('status', 'Active')->avg('staff_behavior'),
            'empathy_and_respect' => Feedbacks::where('status', 'Active')->avg('empathy_and_respect'),
        );
        return response()->json([
            'status' => 'success',
            'feedbacks' => $feedback_rating,
        ]);
    }

    // membership api
    public function postMembershipCard(Request $request){
         
        try{
            $card = MembershipCards::create([
                'name' => $request->name,
                'cnic' => $request->cnic,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'email' => $request->email,
                'membership_type' => $request->membership_type,
                'address' => $request->address,
                'no_of_members' => $request->no_of_members,
                'preferred_hospital' => '',
                'emergency_name' => $request->emergency_name,
                'emergency_contact' => $request->emergency_contact
            ]);
 
            return response()->json([
                'status' => 'success',
                'message' => 'Card request created successfully!',
            ], 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        } 
    }

    public function allDepartments(Request $request){
        $search = $request->query('search');
        if(!empty($search)){
            $departments = Departments::where('name', 'LIKE', '%'.$search.'%')->get();
        }else{
            $departments = Departments::get();
        }
        foreach($departments as $department){
            $department->icon = Storage::disk('public')->url('uploads/departments/'.$department->icon);
        }
        return response()->json([
            'status' => 'success',
            'departments' => $departments,
        ]);
    }
    
   public function getDoctorsByDepartment(Request $request, $id){
    // Get the name from the query parameter
    $name = $request->query('name');

    // Build the query
    $query = Doctors::with('department')->where('department_id', $id);

    // If name is provided, apply the search filter
    if ($name) {
        $query->where('name', 'like', '%' . $name . '%');
    }

    // Get the results
    $doctors = $query->get();

    // Decode timetable and set image URL
    foreach($doctors as $doctor){
        $doctor->timetable = json_decode($doctor->timetable);
        $doctor->image = Storage::disk('public')->url('uploads/doctors/'.$doctor->image);
    }

    return response()->json([
        'status' => 'success',
        'doctors' => $doctors,
    ]);
}


    public function getDoctorsByTags(Request $request){
        $search = $request->query('search');
        $doctors = array();
        if(!empty($search)){
            $doctors = Doctors::with('department')->where('tags', 'LIKE', '%' . $search . '%')->orwhere('name', 'LIKE', '%' . $search . '%')->get();
            foreach($doctors as $doctor){
                $doctor->timetable = json_decode($doctor->timetable);
                $doctor->image = Storage::disk('public')->url('uploads/doctors/'.$doctor->image);
            }
        }
        return response()->json([
            'status' => 'success',
            'doctors' => $doctors,
        ]);
    }

    public function getOneDayDoctorAppointment(Request $request, $doctor_id) {
    //fetch doctor details
    $doctor = Doctors::where('id', $doctor_id)->first();

    if (!$doctor) {
        return response()->json([
            'status' => 'error',
            'message' => 'Doctor not found',
        ], 404);
    }

    $date = $request->query('date');

    if (!$date || !strtotime($date)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid date',
        ], 400);
    }

    // Get day of the week
    $day_of_week = date('l', strtotime($date)); // Full name like Monday

    // Dynamically build the field names
    $isAvailableField = 'IsAvailableOn' . $day_of_week;
    $fromField = $day_of_week . 'From';
    $toField = $day_of_week . 'To';

    // Check if the doctor is available that day
    if (!$doctor->$isAvailableField || !$doctor->$fromField || !$doctor->$toField) {
        return response()->json([
            'status' => 'success',
            'appointments' => [],
        ]);
    }

    try {
       //Parse the start and end times
        $start_time = Carbon::parse($doctor->$fromField);
        $end_time = Carbon::parse($doctor->$toField);

        $appointments = [];
        while ($start_time->lessThan($end_time)) {
            $appointments[] = $start_time->format('g:iA');
            $start_time->addMinutes(20);
        }

        return response()->json([
            'status' => 'success',
            'appointments' => $appointments,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error parsing time: ' . $e->getMessage(),
        ], 500);
    }
}

    
    public function createAppointment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'time_slot' => 'required|date_format:h:iA',
        'doctor_name' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => $validator->errors(),
        ], 400);
    }

    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not authenticated.',
        ], 401);
    }

    $appointment = Appointments::create([
        'user_id' => $user->id,
        'date' => $request->date,
        'time_slot' => $request->time_slot,
        'doctor_name' => $request->doctor_name,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment created successfully!',
    ], 200);
}


public function getLabTests(Request $request)
{
    try {
        $sid = $request->query('SID');
        $serviceName = $request->query('ServiceName');
        $sectionName = $request->query('SectionName');

        $query = DB::table('tblServicesProfile')
            ->join('tblEmployeeSetup', 'tblServicesProfile.EmployeeCode', '=', 'tblEmployeeSetup.EmployeeCode')
            ->join('tblServices', 'tblServicesProfile.ServiceID', '=', 'tblServices.ServiceId')
            ->join('tblSections', 'tblServices.SectionId', '=', 'tblSections.SectionId')
            ->select(
                'tblServicesProfile.ServiceProfileID as SID',
                'tblServices.ServiceName as ServiceName',
                'tblEmployeeSetup.EmployeeName as EmployeeName',
                'tblServicesProfile.NormalFees as NormalFees',
                'tblSections.SectionName',
                'tblServicesProfile.Description'
            );

        if ($sid) {
            $query->where('tblServicesProfile.ServiceProfileID', $sid);
        }

        if ($serviceName) {
            $query->where('tblServices.ServiceName', 'like', '%' . $serviceName . '%');
        }

        if ($sectionName) {
            $query->where('tblSections.SectionName', 'like', '%' . $sectionName . '%');
        }

        $servicesData = $query->get();

        if ($servicesData->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No lab test found for the given criteria.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $servicesData,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function getPharmacyProducts(Request $request)
{
    try {
        $productId = $request->query('ProductId');
        $productName = $request->query('ProductName');
        $salt = $request->query('Salt');

        $query = DB::table('tblChartOfItems as ci')
            ->select(
                'ci.ProductID as ProductId',
                'ci.ProductName',
                'ci.Salt',
                'ci.Pieces',
                'ci.PurchasePrice',
                'ci.SalePrice',
                'ci.Discount',
                'ssi.Qty',
                'ci.Remarks as Rack',
                DB::raw("IFNULL(tblSubCategory.SubCategoryName, '') as SubCategory"),
                'tblBrands.BrandName',
                DB::raw("IFNULL(ci.Status, '') as Status"),
                DB::raw("IFNULL(ci.`Lock`, '') as `Lock`"),
                'ci.up'
            )
            ->join('tblSubStoreInventory as ssi', 'ci.ProductID', '=', 'ssi.PID')
            ->leftJoin('tblBrands', 'tblBrands.BrandId', '=', 'ci.BrandID')
            ->leftJoin('tblCategory', 'ci.CategoryID', '=', 'tblCategory.CategoryID')
            ->leftJoin('tblSubCategory', 'ci.SubCategoryID', '=', 'tblSubCategory.SubCategoryID')
            ->where('ssi.SectionID', 41)
            ->where(DB::raw("IFNULL(ci.Status, '')"), '=', 'Ok')
            ->where('ci.up', '=', '1');

        // If product ID is provided
        if ($productId) {
            $query->where('ci.ProductID', '=', $productId);
        }

        // If product name is provided, find its salt and get all medicines with that salt
        if ($productName) {
            $saltFromName = DB::table('tblChartOfItems')
                ->where('ProductName', 'like', '%' . $productName . '%')
                ->whereNotNull('Salt')
                ->value('Salt');

            if ($saltFromName) {
                $query->where('ci.Salt', '=', $saltFromName);
            } else {
                // Fallback to normal product name search
                $query->where('ci.ProductName', 'like', '%' . $productName . '%');
            }
        }

        // If salt is explicitly provided
        if ($salt) {
            $query->where('ci.Salt', 'like', '%' . $salt . '%');
        }

        $products = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function createLabTestBooking(Request $request)
{
    $request->validate([
        'sids' => 'required|array|min:1',
        'sids.*' => 'integer|exists:tblServicesProfile,ServiceProfileID',
        'address' => 'required|string|max:500', 
    ]);

    try {
        $userId = Auth::id();

        // Fetch NormalFees for each SID
        $totalPrice = DB::table('tblServicesProfile')
            ->whereIn('ServiceProfileID', $request->sids)
            ->sum('NormalFees');

        $booking = LabTestBooking::create([
            'UserID' => $userId,
            'Status' => 'pending',
            'TotalPrice' => $totalPrice,
            'Address' => $request->address, 
        ]);

        foreach ($request->sids as $sid) {
            LabTestBookingDetail::create([
                'BookingID' => $booking->BookingID,
                'ServiceProfileID' => $sid,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Booking created successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Something went wrong: ' . $e->getMessage()
        ], 500);
    }
}


public function getLabTestBookings(Request $request)
{
    try {
        $userId = Auth::id();
        $searchName = $request->query('UserName'); 

        $query = DB::table('tblLabTestBookings')
            ->join('users', 'tblLabTestBookings.UserID', '=', 'users.id')
            ->where('tblLabTestBookings.UserID', $userId)
            ->orderBy('tblLabTestBookings.created_at', 'desc')
            ->select(
                'tblLabTestBookings.*',
                'users.name as UserName',
                'users.phone as UserPhone'
            );

        if ($searchName) {
            $query->where('users.name', 'like', '%' . $searchName . '%');
        }

        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No lab test bookings found.',
                'data' => [],
            ], 200);
        }

        $result = [];

        foreach ($bookings as $booking) {
            $labTests = DB::table('tblLabTestBookingDetails')
                ->join('tblServicesProfile', 'tblLabTestBookingDetails.ServiceProfileID', '=', 'tblServicesProfile.ServiceProfileID')
                ->join('tblEmployeeSetup', 'tblServicesProfile.EmployeeCode', '=', 'tblEmployeeSetup.EmployeeCode')
                ->join('tblServices', 'tblServicesProfile.ServiceID', '=', 'tblServices.ServiceId')
                ->join('tblSections', 'tblServices.SectionId', '=', 'tblSections.SectionId')
                ->where('tblLabTestBookingDetails.BookingID', $booking->BookingID)
                ->select(
                    'tblLabTestBookingDetails.ServiceProfileID as SID',
                    'tblServices.ServiceName',
                    'tblSections.SectionName',
                    'tblEmployeeSetup.EmployeeName',
                    'tblServicesProfile.NormalFees',
                    'tblServicesProfile.Description'
                )
                ->get();

            $totalPrice = $labTests->sum('NormalFees');

            $result[] = [
                'BookingID'   => $booking->BookingID,
                'UserID'      => $booking->UserID,
                'UserName'    => $booking->UserName,
                'UserPhone'   => $booking->UserPhone,
                'Status'      => $booking->Status,
                'Address'      => $booking->Address,
                'created_at'  => $booking->created_at,
                'updated_at'  => $booking->updated_at,
                'TotalPrice'  => $totalPrice,
                'LabTests'    => $labTests,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}



public function createOrder(Request $request)
{
    try {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.ProductID' => 'required|integer|exists:tblChartOfItems,ProductID',
            'items.*.Qty' => 'required|integer|min:1',
            'address' => 'required|string|max:500', 
        ]);

        $userId = auth()->user()->id;

        DB::beginTransaction();

        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = DB::table('tblChartOfItems')->where('ProductID', $item['ProductID'])->first();
            $discount = $product->Discount ?? 0;
            $totalPrice += ($product->SalePrice - $discount) * $item['Qty'];
        }

        $order = Order::create([
            'UserID' => $userId,
            'TotalPrice' => $totalPrice,
            'Status' => 'Pending',
            'Address' => $validated['address'], 
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'OrderID' => $order->OrderID,
                'ProductID' => $item['ProductID'],
                'Qty' => $item['Qty'],
            ]);
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully.',
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function getOrders(Request $request)
{
    try {
        $user = auth()->user();
        $search = $request->query('UserName');

        $orders = Order::with(['user', 'items'])
            ->where('UserID', $user->id)
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedOrders = $orders->map(function ($order) {
            return [
                'OrderID' => $order->OrderID,
                'UserID' => $order->UserID,
                'Name' => $order->user->name ?? '',
                'Phone' => $order->user->phone ?? '',
                'TotalPrice' => $order->TotalPrice,
                'Status' => $order->Status,
                'Address' => $order->Address,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'Items' => $order->items->map(function ($item) {
                    $product = DB::table('tblChartOfItems')->where('ProductID', $item->ProductID)->first();

                    return [
                        'ProductId' => $product->ProductID ?? null,
                        'ProductName' => $product->ProductName ?? '',
                        'Salt' => $product->Salt ?? '',
                        'SalePrice' => $product->SalePrice ?? 0,
                        'Discount' => $product->Discount ?? 0,
                        'Qty' => $item->Qty,
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedOrders,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}



public function getAppointments(Request $request)
{
    try {
        $user = auth()->user();
        $search = $request->query('search');

        $appointments = Appointments::with(['user:id,name,phone'])
            ->where('user_id', $user->id)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('doctor_name', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'user_name' => $appointment->user->name ?? null,
                'user_phone' => $appointment->user->phone ?? null,
                'date' => $appointment->date,
                'time_slot' => $appointment->time_slot,
                'doctor_name' => $appointment->doctor_name,
                'status' => $appointment->status,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}



public function addHealthTip(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'image' => 'required|string', 
            ]);

            $fileName = '';
            if ($request->image) {
                $decodedImage = base64_decode($request->image);
                $fileName = time() . '.png';
                Storage::disk('public')->put('uploads/' . $fileName, $decodedImage);
            }

            $healthTip = HealthTip::create([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $fileName,
            ]);

            return response()->json([
                'status'=>'success',
                'message' => 'Health tip added successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to add health tip',
                'message' => $e->getMessage()
            ], 500);
        }
    }



public function getAllHealthTips(Request $request)
{
    try {
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

       $healthTips = HealthTip::orderBy('created_at', 'desc')->get();

        foreach ($healthTips as $tip) {
            $tip->image = Storage::disk('public')->url('uploads/' . $tip->image);
        }

        return response()->json([
            'status'=>'success',
            'message' => 'Health tips fetched successfully',
            'data' => $healthTips
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to fetch health tips',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function addLabTestReport(Request $request)
{
    try {
        $request->validate([
            'testname' => 'required|string|max:255',
            'report' => 'nullable|url',
        ]);

        $report = LabTestReport::create([
            'user_id' => Auth::id(), // Take from Bearer Token
            'testname' => $request->testname,
            'report' => $request->report
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lab test report added successfully',
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error adding lab test report: ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'Failed to add lab test report',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getLabTestReportsByUser(Request $request)
{
    try {
        $userId = Auth::id();
        $search = $request->query('search');

        $reportsQuery = LabTestReport::with('user:id,name')
            ->where('user_id', $userId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('testname', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function ($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->orderBy('lab_test_reports.created_at', 'desc');

        $reports = $reportsQuery->get()->map(function ($report) {
            return [
                'id' => $report->id,
                'user_id' => $report->user_id,
                'patient_name' => $report->user->name ?? null,
                'testname' => $report->testname,
                'report' => $report->report,
                'created_at' => $report->created_at,
                'updated_at' => $report->updated_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Lab test reports fetched successfully',
            'data' => $reports
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch lab test reports',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getAddresses()
    {
        try {
            $addresses = DB::table('addresses')->get();

            return response()->json([
                'status' => true,
                'data' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch addresses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function deleteLabTestBooking(Request $request)
{
    try {
        $bookingID = $request->query('BookingID');

        if (!$bookingID) {
            return response()->json(['status' => false, 'message' => 'BookingID is required'], 400);
        }

        $booking = LabTestBooking::find($bookingID);

        if (!$booking) {
            return response()->json(['status' => false, 'message' => 'Booking not found'], 404);
        }

        $booking->delete(); 

        return response()->json(['status' => 'success', 'message' => 'Booking deleted successfully'], 200);

    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => 'Error deleting booking', 'error' => $e->getMessage()], 500);
    }
}

public function deleteOrder(Request $request)
{
    try {
        $orderID = $request->query('OrderID');

        if (!$orderID) {
            return response()->json(['status' => false, 'message' => 'OrderID is required'], 400);
        }

        $order = Order::find($orderID);

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $order->delete(); 

        return response()->json(['status' => 'success', 'message' => 'Order deleted successfully'], 200);

    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => 'Error deleting order', 'error' => $e->getMessage()], 500);
    }
}

public function deleteAppointment(Request $request)
{
    $appointmentId = $request->query('id');

    if (!$appointmentId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Appointment ID is required.',
        ], 400);
    }

    $user = Auth::user();
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not authenticated.',
        ], 401);
    }

    $appointment = Appointments::where('id', $appointmentId)
        ->where('user_id', $user->id) 
        ->first();

    if (!$appointment) {
        return response()->json([
            'status' => 'error',
            'message' => 'Appointment not found or access denied.',
        ], 404);
    }

    $appointment->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment deleted successfully.',
    ], 200);
}

public function makePayment(Request $request)
{
    try {
        $request->validate([
            'payment_type' => 'required|string|max:100',
            'payment_image' => 'nullable|string', 
        ]);

        $fileName = '';
        if (isset($request->payment_image) && $request->payment_image) {
            $file = base64_decode($request->payment_image);
            $fileName = time() . '.png';
            Storage::disk('public')->put('uploads/' . $fileName, $file);
        }

        $userId = Auth::id();

        DB::table('user_payments')->insert([
            'user_id' => $userId,
            'payment_type' => $request->payment_type,
            'payment_image' => $fileName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment saved successfully',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to save payment: ' . $e->getMessage()
        ], 500);
    }
}

public function approveAppointment(Request $request)
{
    $appointmentId = $request->query('id');

    if (!$appointmentId) {
        return response()->json([
            'status' => 'error',
            'message' => 'Appointment ID is required in the query parameter.',
        ], 400);
    }

    $appointment = Appointments::find($appointmentId);

    if (!$appointment) {
        return response()->json([
            'status' => 'error',
            'message' => 'Appointment not found.',
        ], 404);
    }

    if ($appointment->status === 'Completed') {
        return response()->json([
            'status' => 'error',
            'message' => 'Appointment is already completed.',
        ], 400);
    }

    $appointment->status = 'Completed';
    $appointment->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Appointment approved and marked as Completed.',
    ], 200);
}

public function createSlider(Request $request)
{
    try {
        $request->validate([
            'image_name' => 'required|string|max:255',
            'image' => 'required|string', // base64 string
            'IsBit' => 'nullable|in:0,1',
        ]);

        $fileName = '';
        if (isset($request->image) && $request->image) {
            $file = base64_decode($request->image);
            $fileName = time() . '.png';
            Storage::disk('public')->put('uploads/' . $fileName, $file);
        }

        $slider = Slider::create([
            'image_name' => $request->image_name,
            'image' => $fileName,
            'IsBit' => $request->IsBit ?? 1, 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Slider created successfully',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to create slider',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getActiveSliders()
{
    try {
        $sliders = Slider::where('IsBit', 1)
                         ->orderBy('id', 'desc') 
                         ->get();

        foreach ($sliders as $slider) {
            $slider->image = Storage::disk('public')->url('uploads/' . $slider->image);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Active sliders fetched successfully',
            'data' => $sliders
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch active sliders',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getProfile(Request $request)
{
    try {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        if ($user->profile_image) {
            $user->profile_image = Storage::disk('public')->url('uploads/' . $user->profile_image);
        } else {
            $user->profile_image = null;
        }

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function updateProfile(Request $request)
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'profile_image' => 'sometimes|string', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->has('profile_image') && $request->profile_image) {
            if ($user->profile_image && Storage::disk('public')->exists('uploads/' . $user->profile_image)) {
                Storage::disk('public')->delete('uploads/' . $user->profile_image);
            }

            $file = base64_decode($request->profile_image);
            $fileName = time() . '.png';
            Storage::disk('public')->put('uploads/' . $fileName, $file);
            $user->profile_image = $fileName;
        }

        $user->save();

        $user->profile_image = $user->profile_image
            ? Storage::disk('public')->url('uploads/' . $user->profile_image)
            : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'user' => $user,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function deleteUser(Request $request)
{
    try {
        $user = Auth::user(); 

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $user->delete(); 

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getOrderTracking(Request $request)
{
    $user = auth()->user(); 

    $orderId = $request->query('OrderID');

    $order = DB::table('tblOrders')->where('OrderID', $orderId)->first();

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found'
        ], 404);
    }

    if ($order->UserID != $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access to this order'
        ], 403);
    }

    $statusList = [
        "Order Placed",
        "Order Confirmed",
        "In Process",
        "Delivered",
    ];

    $statusDescriptions = [
        "Your order has been placed successfully. We have received your order and it's being processed.",
        "Your order has been confirmed by the seller. We are preparing your items.",
        "Your order has been packed and is on its way for delivery.",
        "Your order has been delivered. Thank you for your order!",
    ];

    $statusMap = [
        'Pending' => 1,
        'Completed' => 2,
        'In Process' => 3,
        'Delivered' => 4
    ];

    $currentLevel = $statusMap[$order->Status] ?? 1;

    $tracking = [];

    for ($i = 0; $i < $currentLevel; $i++) {
        $tracking[] = [
            'order_track' => $statusList[$i],
            'description' => $statusDescriptions[$i],
        ];
    }

    return response()->json([
        'status' => 'success',
        'data' => $tracking
    ], 200);
}

public function getLabBookingTracking(Request $request)
{
    $user = auth()->user(); 

    $bookingId = $request->query('BookingID');

    $booking = DB::table('tblLabTestBookings')->where('BookingID', $bookingId)->first();

    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Booking not found'
        ], 404);
    }

    if ($booking->UserID != $user->id) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access to this booking'
        ], 403);
    }

    $statusList = [
        "Order Placed",
        "Order Confirmed",
        "In Process",
        "Delivered",
    ];

    $statusDescriptions = [
        "Your booking has been placed successfully. We have received your request and it's being processed.",
        "Your booking has been confirmed by the lab. Preparation is underway.",
        "Your tests are being processed and results will be delivered shortly.",
        "Your lab test has been completed and delivered. Thank you!",
    ];

    $statusMap = [
        'Pending' => 1,
        'Completed' => 2,
        'In Process' => 3,
        'Delivered' => 4
    ];

    $currentLevel = $statusMap[$booking->Status] ?? 1;

    $tracking = [];

    for ($i = 0; $i < $currentLevel; $i++) {
        $tracking[] = [
            'booking_track' => $statusList[$i],
            'description' => $statusDescriptions[$i],
        ];
    }

    return response()->json([
        'status' => 'success',
        'data' => $tracking
    ],200);
}

public function addBloodSugarReading(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'Date' => 'required|date',
            'Time' => 'required|date_format:h:iA',
            'Sugar' => 'required|integer',
            'Interval' => 'nullable|string|max:255',
            'Comments' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        $reading = BloodSugar::create([
            'user_id' => Auth::id(), 
            'Date' => $request->Date,
            'Time' => $convertedTime,
            'Sugar' => $request->Sugar,
            'Interval' => $request->Interval,
            'Comments' => $request->Comments,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Blood sugar reading saved successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}

public function updateBloodSugarReading(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['status' => false, 'message' => 'ID is required in query parameter.'], 400);
        }

        $reading = BloodSugar::find($id);

        if (!$reading || $reading->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Record not found or unauthorized.'], 404);
        }

        $validated = Validator::make($request->all(), [
            'Date' => 'nullable|date',
            'Time' => 'nullable|date_format:h:iA',
            'Sugar' => 'nullable|integer',
            'Interval' => 'nullable|string|max:255',
            'Comments' => 'nullable|string|max:255',
        ]);

        if ($validated->fails()) {
            return response()->json(['status' => false, 'errors' => $validated->errors()], 422);
        }

        if ($request->has('Time')) {
            $request->merge([
                'Time' => \Carbon\Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s')
            ]);
        }

        $reading->update($request->only([
            'Date', 'Time', 'Sugar', 'Interval', 'Comments'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Blood sugar reading updated successfully.',
        ],200);

    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}


public function getAllBloodSugarReadings()
{
    try {
        $readings = BloodSugar::where('user_id', Auth::id())->get()->map(function ($item) {
            $item->Time = \Carbon\Carbon::createFromFormat('H:i:s', $item->Time)->format('h:iA');
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $readings
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

public function deleteBloodSugarById(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['status' => false, 'message' => 'ID is required in query parameter.'], 400);
        }

        $reading = BloodSugar::find($id);

        if (!$reading || $reading->user_id !== Auth::id()) {
            return response()->json(['status' => false, 'message' => 'Record not found or unauthorized.'], 404);
        }

        $reading->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Blood sugar reading deleted successfully.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
    }
}



public function storeBloodPressure(Request $request)
{
    $validator = Validator::make($request->all(), [
        'Date' => 'required|date',
        'Time' => 'required|date_format:h:iA',
        'Systolic_Pressure' => 'required|integer',
        'Diastolic_Pressure' => 'required|integer',
        'Pulse_Rate' => 'required|integer',
        'Handside' => 'nullable|string|max:255',
        'Comments' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Get authenticated user ID
        $userId = auth()->id(); // or $request->user()->id;

        // Convert time to 24-hour format
        $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        // Store blood pressure record
        $bp = BloodPressure::create([
            'user_id' => $userId,
            'Date' => $request->Date,
            'Time' => $convertedTime,
            'Systolic_Pressure' => $request->Systolic_Pressure,
            'Diastolic_Pressure' => $request->Diastolic_Pressure,
            'Pulse_Rate' => $request->Pulse_Rate,
            'Handside' => $request->Handside,
            'Comments' => $request->Comments,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Blood pressure data stored successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to store data.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function updateBloodPressure(Request $request)
{
    $id = $request->query('id');

    if (!$id) {
        return response()->json(['status' => false, 'message' => 'ID query parameter is required'], 400);
    }

    $bp = BloodPressure::find($id);

    if (!$bp) {
        return response()->json(['status' => false, 'message' => 'Record not found'], 404);
    }

    if ($bp->user_id !== auth()->id()) {
        return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
    }

    $validated = Validator::make($request->all(), [
        'Date' => 'nullable|date',
        'Time' => 'nullable|date_format:h:iA',
        'Systolic_Pressure' => 'nullable|integer',
        'Diastolic_Pressure' => 'nullable|integer',
        'Pulse_Rate' => 'nullable|integer',
        'Handside' => 'nullable|string|max:255',
        'Comments' => 'nullable|string|max:255',
    ]);

    if ($validated->fails()) {
        return response()->json(['status' => false, 'errors' => $validated->errors()], 422);
    }

    try {
        $data = $request->all();

        if (isset($data['Time'])) {
            $data['Time'] = Carbon::createFromFormat('h:iA', $data['Time'])->format('H:i:s');
        }

        $bp->update($data);

        return response()->json(['status' => 'success', 'message' => 'Record updated successfully'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'message' => 'Failed to update', 'error' => $e->getMessage()], 500);
    }
}


public function getBloodPressures()
{
    $userId = auth()->id();

    $data = BloodPressure::where('user_id', $userId)->get()->map(function ($item) {
        $item->Time = Carbon::createFromFormat('H:i:s', $item->Time)->format('h:iA');
        return $item;
    });

    return response()->json([
        'status' => 'success',
        'data' => $data
    ], 200);
}


public function deleteBloodPressure(Request $request)
{
    $id = $request->query('id');

    if (!$id) {
        return response()->json(['status' => false, 'message' => 'ID query parameter is required'], 400);
    }

    $bp = BloodPressure::find($id);

    if (!$bp) {
        return response()->json(['status' => false, 'message' => 'Record not found'], 404);
    }

    if ($bp->user_id !== auth()->id()) {
        return response()->json(['status' => false, 'message' => 'Unauthorized access'], 403);
    }

    $bp->delete();

    return response()->json(['status' => 'success', 'message' => 'Record deleted successfully'], 200);
}



public function addBodyTemperature(Request $request)
{
    try {
        // Validate request input
        $request->validate([
            'Date' => 'required|date',
            'Time' => 'required|date_format:h:iA',
            'Body_Temperature' => 'required|integer',
            'Comments' => 'nullable|string|max:255'
        ]);

        // Convert time to 24-hour format
        $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        // Get authenticated user ID
        $userId = auth()->user()->id;

        // Create a new body temperature record
        $data = BodyTemperature::create([
            'user_id' => $userId,
            'Date' => $request->Date,
            'Time' => $convertedTime,
            'Body_Temperature' => $request->Body_Temperature,
            'Comments' => $request->Comments
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Body temperature added successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function updateBodyTemperature(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['error' => 'ID query parameter is required'], 400);
        }

        $temperature = BodyTemperature::find($id);
        if (!$temperature) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        if ($temperature->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'Date' => 'sometimes|date',
            'Time' => 'sometimes|date_format:h:iA',
            'Body_Temperature' => 'sometimes|integer',
            'Comments' => 'sometimes|string|max:255'
        ]);

        if (isset($validated['Time'])) {
            $validated['Time'] = Carbon::createFromFormat('h:iA', $validated['Time'])->format('H:i:s');
        }

        $temperature->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Body temperature updated successfully'], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function getBodyTemperatures()
{
    try {
        $userId = auth()->id();
        $data = BodyTemperature::where('user_id', $userId)->get();

        foreach ($data as $item) {
            $item->Time = Carbon::createFromFormat('H:i:s', $item->Time)->format('h:iA');
        }

        return response()->json(['status' => 'success', 'data' => $data], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function deleteBodyTemperature(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['error' => 'ID query parameter is required'], 400);
        }

        $temperature = BodyTemperature::find($id);
        if (!$temperature) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        if ($temperature->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $temperature->delete();

        return response()->json(['status' => 'success', 'message' => 'Body temperature record deleted'], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function addBloodOxygen(Request $request)
{
    try {
        $request->validate([
            'Date' => 'required|date',
            'Time' => 'required|date_format:h:iA',
            'Blood_Oxygen_Saturation' => 'required|integer',
            'Comments' => 'nullable|string|max:255',
        ]);

        $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        $bloodOxygen = BloodOxygen::create([
            'user_id' => auth()->id(),
            'Date' => $request->Date,
            'Time' => $convertedTime,
            'Blood_Oxygen_Saturation' => $request->Blood_Oxygen_Saturation,
            'Comments' => $request->Comments,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Blood oxygen record added successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to add blood oxygen record.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function updateBloodOxygen(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer|exists:blood_oxygen,id',
            'Date' => 'sometimes|date',
            'Time' => 'sometimes|date_format:h:iA',
            'Blood_Oxygen_Saturation' => 'sometimes|integer',
            'Comments' => 'sometimes|string|max:255',
        ]);

        $bloodOxygen = BloodOxygen::where('id', $request->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($request->has('Time')) {
            $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');
            $bloodOxygen->Time = $convertedTime;
        }

        if ($request->has('Date')) {
            $bloodOxygen->Date = $request->Date;
        }

        if ($request->has('Blood_Oxygen_Saturation')) {
            $bloodOxygen->Blood_Oxygen_Saturation = $request->Blood_Oxygen_Saturation;
        }

        if ($request->has('Comments')) {
            $bloodOxygen->Comments = $request->Comments;
        }

        $bloodOxygen->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Record updated successfully.',
        ],200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Update failed or access denied.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function getBloodOxygen()
{
    try {
        $data = BloodOxygen::where('user_id', auth()->id())->get();

        foreach ($data as $item) {
            if ($item->Time) {
                $item->Time = Carbon::createFromFormat('H:i:s', $item->Time)->format('h:iA');
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ],200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch data.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function deleteBloodOxygen(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer|exists:blood_oxygen,id',
        ]);

        $record = BloodOxygen::where('id', $request->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $record->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Record deleted successfully.'
        ],200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Delete failed or access denied.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function storeHemoglobin(Request $request)
{
    try {
        $request->validate([
            'Date' => 'required|date',
            'Time' => 'required|date_format:h:iA',
            'Measurement_Type' => 'required|string|max:255',
            'Sugar_Concentration' => 'required|integer',
            'Comments' => 'nullable|string',
        ]);

        $convertedTime = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        $userId = $request->user()->id;

        $hemoglobin = Hemoglobin::create([
            'user_id' => $userId,
            'Date' => $request->Date,
            'Time' => $convertedTime,
            'Measurement_Type' => $request->Measurement_Type,
            'Sugar_Concentration' => $request->Sugar_Concentration,
            'Comments' => $request->Comments,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hemoglobin record inserted successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to store hemoglobin data.',
            'message' => $e->getMessage()
        ], 500);
    }
}


public function updateHemoglobin(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['error' => 'ID query parameter is required.'], 400);
        }

        $userId = $request->user()->id;

        // Fetch only if the record belongs to the authenticated user
        $hemoglobin = Hemoglobin::where('id', $id)->where('user_id', $userId)->first();

        if (!$hemoglobin) {
            return response()->json(['error' => 'Hemoglobin record not found or unauthorized.'], 404);
        }

        $validated = $request->validate([
            'Date' => 'sometimes|date',
            'Time' => 'sometimes|date_format:h:iA',
            'Measurement_Type' => 'sometimes|string|max:255',
            'Sugar_Concentration' => 'sometimes|integer',
            'Comments' => 'sometimes|string|nullable',
        ]);

        if (isset($validated['Time'])) {
            $validated['Time'] = Carbon::createFromFormat('h:iA', $validated['Time'])->format('H:i:s');
        }

        $hemoglobin->update($validated);

        return response()->json([
            'status'=>'success',
            'message' => 'Hemoglobin record updated successfully.',
        ],200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Update failed.',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function getHemoglobin(Request $request)
{
    try {
        $userId = $request->user()->id;

        $data = Hemoglobin::where('user_id', $userId)->get();

        foreach ($data as $item) {
            if ($item->Time) {
                $item->Time = Carbon::createFromFormat('H:i:s', $item->Time)->format('h:iA');
            }
        }

        return response()->json([
            'status'=>'success',
            'data' => $data
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Fetch failed.',
            'message' => $e->getMessage()
        ], 500);
    }
}

 
public function deleteHemoglobin(Request $request)
{
    try {
        $id = $request->query('id');

        if (!$id) {
            return response()->json(['error' => 'ID query parameter is required.'], 400);
        }

        $userId = $request->user()->id;

        $hemoglobin = Hemoglobin::where('id', $id)->where('user_id', $userId)->first();

        if (!$hemoglobin) {
            return response()->json(['error' => 'Hemoglobin record not found or unauthorized.'], 404);
        }

        $hemoglobin->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Hemoglobin record deleted successfully.'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Deletion failed.',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function storeUserWeight(Request $request)
{
    try {
        // Validate incoming request
        $request->validate([
            'Date' => 'required|date',
            'Time' => 'required|date_format:h:iA',
            'Weight' => 'required|integer',
            'Comments' => 'nullable|string|max:255',
        ]);

        // Convert time format from h:iA to H:i:s
        $convertedTime = \Carbon\Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');

        // Create new weight entry
        $userWeight = \App\Models\UserWeight::create([
            'user_id' => auth()->id(),
            'date' => $request->Date,
            'time' => $convertedTime,
            'weight' => $request->Weight,
            'comments' => $request->Comments,
        ]);

        return response()->json([
            'status'=>'success',
            'message' => 'Weight entry created successfully',
        ], 200);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function updateUserWeight(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer',
            'Date' => 'nullable|date',
            'Time' => 'nullable|date_format:h:iA',
            'Weight' => 'nullable|integer',
            'Comments' => 'nullable|string|max:255',
        ]);

        $userId = auth()->id();
        $weightEntry = UserWeight::where('id', $request->id)->where('user_id', $userId)->first();

        if (!$weightEntry) {
            return response()->json(['message' => 'Record not found or unauthorized'], 404);
        }

        if ($request->has('Date')) {
            $weightEntry->date = $request->Date;
        }
        if ($request->has('Time')) {
            $weightEntry->time = Carbon::createFromFormat('h:iA', $request->Time)->format('H:i:s');
        }
        if ($request->has('Weight')) {
            $weightEntry->weight = $request->Weight;
        }
        if ($request->has('Comments')) {
            $weightEntry->comments = $request->Comments;
        }

        $weightEntry->save();

        return response()->json(['status'=>'success','message' => 'Weight entry updated successfully'], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
    }
}

public function getUserWeights()
{
    try {
        $userId = auth()->id();
        $data = UserWeight::where('user_id', $userId)->get();

        foreach ($data as $item) {
            if ($item->time) {
                $item->time = Carbon::createFromFormat('H:i:s', $item->time)->format('h:iA');
            }
        }

        return response()->json(['status'=>'success','data' => $data], 200);

    } catch (\Exception $e) {
        return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
    }
}

public function deleteUserWeight(Request $request)
{
    try {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $userId = auth()->id();
        $entry = UserWeight::where('id', $request->id)->where('user_id', $userId)->first();

        if (!$entry) {
            return response()->json(['message' => 'Record not found or unauthorized'], 404);
        }

        $entry->delete();

        return response()->json(['status'=>'success','message' => 'Weight entry deleted successfully'], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
    }
}

public function getAllAppointments(Request $request)
{
    try {
        $search = $request->query('search');

        $appointments = Appointments::with(['user:id,name,phone'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('doctor_name', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', '%' . $search . '%');
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $appointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'user_name' => $appointment->user->name ?? null,
                'user_phone' => $appointment->user->phone ?? null,
                'date' => $appointment->date,
                'time_slot' => $appointment->time_slot,
                'doctor_name' => $appointment->doctor_name,
                'status' => $appointment->status,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function getAllLabTestBookings(Request $request)
{
    try {
        $searchName = $request->query('UserName'); 

        $query = DB::table('tblLabTestBookings')
            ->join('users', 'tblLabTestBookings.UserID', '=', 'users.id')
            ->orderBy('tblLabTestBookings.created_at', 'desc')
            ->select(
                'tblLabTestBookings.*',
                'users.name as UserName',
                'users.phone as UserPhone'
            );

        if ($searchName) {
            $query->where('users.name', 'like', '%' . $searchName . '%');
        }

        $bookings = $query->get();

        if ($bookings->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No lab test bookings found.',
                'data' => [],
            ], 200);
        }

        $result = [];

       foreach ($bookings as $booking) {
    $labTests = DB::table('tblLabTestBookingDetails')
        ->join('tblServicesProfile', 'tblLabTestBookingDetails.ServiceProfileID', '=', 'tblServicesProfile.ServiceProfileID')
        ->join('tblEmployeeSetup', 'tblServicesProfile.EmployeeCode', '=', 'tblEmployeeSetup.EmployeeCode')
        ->join('tblServices', 'tblServicesProfile.ServiceID', '=', 'tblServices.ServiceId')
        ->join('tblSections', 'tblServices.SectionId', '=', 'tblSections.SectionId')
        ->where('tblLabTestBookingDetails.BookingID', $booking->BookingID)
        ->select(
            'tblLabTestBookingDetails.ServiceProfileID as SID',
            'tblServices.ServiceName',
            'tblSections.SectionName',
            'tblEmployeeSetup.EmployeeName',
            'tblServicesProfile.NormalFees',
            'tblServicesProfile.Description'
        )
        ->get();

    $totalPrice = $labTests->sum('NormalFees');

    $formattedCreatedAt = Carbon::parse($booking->created_at)->format('d-m-Y h:i A');
    $formattedUpdatedAt = Carbon::parse($booking->updated_at)->format('d-m-Y h:i A');

    $result[] = [
        'BookingID'   => $booking->BookingID,
        'UserID'      => $booking->UserID,
        'UserName'    => $booking->UserName,
        'UserPhone'   => $booking->UserPhone,
        'Status'      => $booking->Status,
        'Address'     => $booking->Address,
        'created_at'  => $formattedCreatedAt,
        'updated_at'  => $formattedUpdatedAt,
        'TotalPrice'  => $totalPrice,
        'LabTests'    => $labTests,
    ];
}
     //Success response in json format
        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function getAllOrders(Request $request)
{
    try {
        $search = $request->query('UserName');

        $orders = Order::with(['user', 'items'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedOrders = $orders->map(function ($order) {
    return [
        'OrderID' => $order->OrderID,
        'UserID' => $order->UserID,
        'Name' => $order->user->name ?? '',
        'Phone' => $order->user->phone ?? '',
        'TotalPrice' => $order->TotalPrice,
        'Status' => $order->Status,
        'Address' => $order->Address,
        'created_at' => $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d-m-Y h:i A') : null,
        'updated_at' => $order->updated_at ? \Carbon\Carbon::parse($order->updated_at)->format('d-m-Y h:i A') : null,
        'Items' => $order->items->map(function ($item) {
            $product = DB::table('tblChartOfItems')->where('ProductID', $item->ProductID)->first();

            return [
                'ProductId' => $product->ProductID ?? null,
                'ProductName' => $product->ProductName ?? '',
                'Salt' => $product->Salt ?? '',
                'SalePrice' => $product->SalePrice ?? 0,
                'Discount' => $product->Discount ?? 0,
                'Qty' => $item->Qty,
            ];
        }),
    ];
});

        return response()->json([
            'status' => 'success',
            'data' => $formattedOrders,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
}


public function getAllPayments(Request $request)
{
    try {
        $search = $request->query('UserName');

        $query = DB::table('user_payments')
            ->join('users', 'user_payments.user_id', '=', 'users.id')
            ->select(
                'user_payments.id',
                'user_payments.user_id',
                'users.name as username',
                'user_payments.payment_type',
                'user_payments.payment_image',
                'user_payments.created_at',
                 'user_payments.updated_at',
            )
            ->orderBy('user_payments.created_at', 'desc');

        // If search by username
        if (!empty($search)) {
            $query->where('users.name', 'like', '%' . $search . '%');
        }

        $payments = $query->get();

        // Append full URL for payment_image
        $payments->transform(function ($payment) {
            if ($payment->payment_image) {
                $payment->payment_image = Storage::disk('public')->url('uploads/' . $payment->payment_image);
            } else {
                $payment->payment_image = null;
            }
            return $payment;
        });

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to retrieve payments: ' . $e->getMessage(),
        ], 500);
    }
}



public function approveLabTest(Request $request)
{
    try {
        $bookingId = $request->query('BookingID');

        if (!$bookingId) {
            return response()->json(['message' => 'Booking ID is required.'], 400);
        }

        $booking = DB::table('tblLabTestBookings')->where('BookingID', $bookingId)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->Status !== 'Pending') {
            return response()->json(['message' => 'Booking is not in Pending status.'], 400);
        }

        DB::table('tblLabTestBookings')->where('BookingID', $bookingId)->update([
            'Status' => 'Completed',
            'updated_at' => now(),
        ]);

        return response()->json(['status'=>'success', 'message' => 'Lab Test Booking approved successfully.'], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function approveOrder(Request $request)
{
    try {
        $orderId = $request->query('OrderID');

        if (!$orderId) {
            return response()->json(['message' => 'Order ID is required.'], 400);
        }

        $order = DB::table('tblOrders')->where('OrderID', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->Status !== 'Pending') {
            return response()->json(['message' => 'Order is not in Pending status.'], 400);
        }

        DB::table('tblOrders')->where('OrderID', $orderId)->update([
            'Status' => 'Completed',
            'updated_at' => now(),
        ]);

        return response()->json(['status'=>'success','message' => 'Pharmacy Order approved successfully.'], 200);

    } catch (Exception $e) {
        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}




}
