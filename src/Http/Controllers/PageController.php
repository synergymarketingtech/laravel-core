<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\Offer;
use App\Enum\AppStatus;
use App\Models\Core\File;
use App\Models\AppSetting;
use App\Models\Plan\Price;
use App\Models\Announcement;
use App\Models\Core\Enquiry;
use App\Rules\ReCaptchaRule;
use Illuminate\Http\Request;
use App\Events\MembershipCreated;

class PageController extends Controller
{
    public function index()
    {
        $offers = Offer::onlyActive()->orderBy('created_at', 'desc')->get();
        return view('pages.home.2', [
            'offers' => $offers
        ]);
    }

    public function membership()
    {
        $plans = Plan::onlyActive()->get();
        return view('pages.membership', [
            'title' => 'Membership',
            'subtitle' => 'Service',
            'background' => 'title-row-2',
            'plans' => $plans,
        ]);
    }

    public function opening_times(Request $request)
    {
        $announcements = Announcement::active()->paginate(10);
        $openingTimes = opening_times();
        return view('pages.opening-times', [
            'title' => 'Opening Times',
            'subtitle' => 'Company',
            'background' => 'title-row-2',
            'announcements' => $announcements,
            'openingTimes' => $openingTimes,
        ]);
    }

    public function documents()
    {
        $documents = File::whereIn('id', AppSetting::findByKey('documents')
            ->where('is_active', true)
            ->where('member', false)
            ->pluck('id'))->get();
        return view('pages.documents', [
            'title' => 'Documents',
            'subtitle' => 'Company',
            'background' => 'title-row-2',
            'documents' => $documents,
        ]);
    }

    public function classes(Request $request)
    {
        return view("pages.classes", [
            'title' => 'Classes',
            'subtitle' => 'Schedule',
            'background' => 'title-row-2',
        ]);
    }

    public function about()
    {
        return view('pages.about-us', [
            'title' => 'About us',
            'subtitle' => 'About us',
            'background' => 'title-row-2',
        ]);
    }

    public function contact()
    {
        return view('pages.contact-us', [
            'title' => 'Contact us',
            'subtitle' => 'Contact us',
            'background' => 'title-row-2',
        ]);
    }

    public function contact_submit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'phone' => 'required',
            'message' => 'required',
            'recaptcha_token' => ['required', new ReCaptchaRule()]
        ]);

        Enquiry::create($request->only([
            'email',
            'name',
            'phone',
            'message',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Message sent. We will contact you soon.'
        ], 200);
    }

    public function terms()
    {
        return view('pages.terms', [
            'title' => 'Terms & Conditions',
            'subtitle' => 'Company',
            'background' => 'title-row-2',
        ]);
    }

    public function privacy()
    {
        return view('pages.privacy', [
            'title' => 'Privacy Policy',
            'subtitle' => 'Company',
            'background' => 'title-row-2',
        ]);
    }

    public function partners()
    {
        return view('pages.partners', [
            'title' => 'Partners',
            'subtitle' => 'Company',
            'background' => 'title-row-2',
        ]);
    }

    public function plans()
    {
        return Plan::with('prices')->onlyActive()->get();
    }
}
