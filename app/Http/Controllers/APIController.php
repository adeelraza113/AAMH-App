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

class APIController extends Controller
{
    public function login(Request $request){
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid login details'
            ], 401);
        }

        try {
            $user = User::where('email', $request['email'])->firstOrFail();
            // Code that may throw an exception
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function users(Request $request){

        $user = User::all();
        return response()->json([
            'users' => $user,
        ]);
    }

    public function register(Request $request){
         
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'unique:users',
            ]);
        
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "User already exist!",
                ]);
            }
            $fileName = "";
            if(isset($request->profile_image) && $request->profile_image){
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
                'password' => bcrypt($request->password),
            ]);
 
            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully!',
            ], 200);
        }catch (Exception $e) {
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
        $doctors = Doctors::with('department')->where('department_id', $id)->get();
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
        // Fetch doctor details
        $doctor = Doctors::where('id', $doctor_id)->first();
    
        // Check if doctor exists
        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Doctor not found',
            ], 404);
        }
    
        // Check if timetable exists
        if (empty($doctor->timetable)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timetable not found for this doctor',
            ], 404);
        }
    
        // Decode the timetable
        $time_table = json_decode($doctor->timetable, true);
        $date = $request->query('date');
        $day_of_week = date('w', strtotime($date));
    
        // Days array to map numeric day to string
        $days = [
            1 => "Monday",
            2 => "Tuesday",
            3 => "Wednesday",
            4 => "Thursday",
            5 => "Friday",
            6 => "Saturday",
            0 => "Sunday"
        ];
    
        // Check if timetable has an entry for the selected day
        if (!isset($time_table[$days[$day_of_week]])) {
            return response()->json([
                'status' => 'error',
                'message' => 'No timetable entry found for this day',
            ], 404);
        }
    
        $selected_date_time_table = $time_table[$days[$day_of_week]];
    
        // Handle if doctor is off on the selected day
        if ($selected_date_time_table == "off") {
            return response()->json([
                'status' => 'success',
                'appointments' => [],
            ]);
        } else {
            // Parse the available time slots
            $selected_date_time_table = explode(' to ', $selected_date_time_table);
            $start_time = Carbon::parse($selected_date_time_table[0]);
            $end_time = Carbon::parse($selected_date_time_table[1]);
    
            $appointments = [];
            while ($start_time->lessThan($end_time)) {
                $appointments[] = $start_time->format('g:iA');
                // Increment by 20 minutes
                $start_time->addMinutes(20);
            }
    
            return response()->json([
                'status' => 'success',
                'appointments' => $appointments,
            ]);
        }
    }

    public function createAppointment(Request $request)
{
    $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'time_slot' => 'required|date_format:h:i A',  
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
    return response()->json([
        'status' => 'success',
        'message' => 'Appointment data received successfully!',
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

        if ($productId) {
            $query->where('ci.ProductID', '=', $productId);
        }

        if ($productName) {
            $query->where('ci.ProductName', 'like', '%' . $productName . '%');
        }

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
        'sids.*' => 'integer|exists:tblServicesProfile,ServiceProfileID'
    ]);

    try {
        $userId = Auth::id();

        // Fetch NormalFees for each SID
        $totalPrice = DB::table('tblServicesProfile')
            ->whereIn('ServiceProfileID', $request->sids)
            ->sum('NormalFees');

        // Create main booking with TotalPrice
        $booking = LabTestBooking::create([
            'UserID' => $userId,
            'Status' => 'pending',
            'TotalPrice' => $totalPrice,
        ]);

        // Insert booking details
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

        $bookings = DB::table('tblLabTestBookings')
            ->join('users', 'tblLabTestBookings.UserID', '=', 'users.id')
            ->where('tblLabTestBookings.UserID', $userId)
            ->select(
                'tblLabTestBookings.*',
                'users.name as UserName',
                'users.phone as UserPhone'
            )
            ->get();

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
        ]);

        $userId = auth()->user()->id;

        DB::beginTransaction();

        // Calculate total price
        $totalPrice = 0;
        foreach ($validated['items'] as $item) {
            $product = DB::table('tblChartOfItems')->where('ProductID', $item['ProductID'])->first();
            $discount = $product->Discount ?? 0;
            $totalPrice += ($product->SalePrice - $discount) * $item['Qty'];
        }

        // Create Order
        $order = Order::create([
            'UserID' => $userId,
            'TotalPrice' => $totalPrice,
            'Status' => 'Pending',
        ]);

        // Create Order Items
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

public function getOrders()
{
    try {
        $orders = Order::with(['user', 'items'])->get();

        $formattedOrders = $orders->map(function ($order) {
            return [
                'OrderID' => $order->OrderID,
                'UserID' => $order->UserID,
                'Name' => $order->user->name ?? '',
                'Phone' => $order->user->phone ?? '',
                'TotalPrice' => $order->TotalPrice,
                'Status' => $order->Status,
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




}