<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMarketingCampaignMail;
class MarketingCampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::with('customerGroup')
            ->withCount('logs')
            ->latest()
            ->get();

        // Chuẩn hóa dữ liệu để gửi về view (array ready for JSON)
        $campaignData = $campaigns->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'target' => optional($c->customerGroup)->name ?? 'Chưa xác định',
                'status' => $c->status,
                'sentDate' => $c->sent_at ? Carbon::parse($c->sent_at)->format('d-m-Y') : null,
                'scheduledAt' => $c->scheduled_at ? Carbon::parse($c->scheduled_at)->format('d/m/Y H:i') : null,
                'logs_count' => $c->logs_count,
            ];
        });
        // dd($campaignData->toArray());

        return view('admin.marketing_campaigns.index', [
            'campaignData' => $campaignData,
        ]);
    }
    public function create(){
        $customerGroups = \App\Models\CustomerGroup::withCount('users')->get();
        $coupons = Coupon::where('status', 'active')->get();
        // dd($customerGroups->toArray());
        return view('admin.marketing_campaigns.create', compact('customerGroups','coupons'));
    }
    // lưu nháp
    public function storeDraft(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255|unique:marketing_campaigns,name',
            'subject'           => 'required|string|max:255',
            'content'           => 'required|string',
            'customer_group_id' => 'required|exists:customer_groups,id',
            'type'              => 'required|in:email,sms',
            'coupon_id'         => 'nullable|exists:coupons,id',
            'status'            => 'required|in:draft,scheduled,sent',
            'scheduled_at'      => 'nullable|date',
        ], [
            'name.required' => 'Tên chiến dịch là bắt buộc.',
            'name.max' => 'Tên chiến dịch không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên chiến dịch này đã tồn tại, vui lòng chọn tên khác.',

            'subject.required' => 'Tiêu đề Email là bắt buộc.',
            'subject.max' => 'Tiêu đề Email không được vượt quá 255 ký tự.',

            'content.required' => 'Nội dung Email là bắt buộc.',

            'customer_group_id.required' => 'Vui lòng chọn nhóm khách hàng.',
            'customer_group_id.exists' => 'Nhóm khách hàng không hợp lệ.',

            'type.required' => 'Kênh gửi là bắt buộc.',
            'type.in' => 'Kênh gửi không hợp lệ.',

            'coupon_id.exists' => 'Mã giảm giá không hợp lệ.',

            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',

            'scheduled_at.date' => 'Thời gian lên lịch không hợp lệ.',
        ]);

        // Nếu trạng thái là scheduled thì bắt buộc phải có scheduled_at và thời gian phải lớn hơn hiện tại
        if (in_array($validated['status'], ['scheduled', 'draft']) && !empty($validated['scheduled_at'])) {
            if (strtotime($validated['scheduled_at']) <= time()) {
                return response()->json([
                    'message' => 'Thời gian gửi phải lớn hơn thời gian hiện tại.'
                ], 422);
            }
        } else if ($validated['status'] === 'scheduled' && empty($validated['scheduled_at'])) {
            return response()->json([
                'message' => 'Thời gian gửi theo lịch là bắt buộc khi trạng thái là lên lịch.'
            ], 422);
        } else if ($validated['status'] === 'sent') {
            $validated['scheduled_at'] = null;
        }
        

        $campaign = new MarketingCampaign();
        $campaign->name = $validated['name'];
        $campaign->email_subject = $validated['subject'];
        $campaign->email_content = $validated['content'];
        $campaign->customer_group_id = $validated['customer_group_id'];
        $campaign->channel = $validated['type'];
        $campaign->coupon_id = $validated['coupon_id'] ?? null;
        $campaign->status = $validated['status'];
        $campaign->scheduled_at = $validated['scheduled_at'];
        $campaign->save();

        return response()->json([
            'message' => $validated['status'] == 'draft'
                ? 'Chiến dịch đã được lưu nháp thành công!'
                : ($validated['status'] == 'scheduled'
                    ? 'Chiến dịch đã được lên lịch thành công!'
                    : 'Chiến dịch đã được gửi thành công!'),
            'campaign' => $campaign,
        ]);
    }
    
    // xóa mềm
    public function destroy($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);
        $campaign->delete();  // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Chiến dịch đã được xóa!Nếu bạn muốn khôi phục có thể vào thùng rác.'
        ]);
    }

    // thùng rác
    public function trash()
    {
        $campaigns = MarketingCampaign::onlyTrashed()->get();

        $campaignData = $campaigns->map(function ($campaign) {
            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'target' => optional($campaign->customerGroup)->name ?? 'Không xác định',
                'status' => $campaign->status,
                'sentDate' => $campaign->sent_at ? \Carbon\Carbon::parse($campaign->sent_at)->format('d/m/Y H:i') : null,
            ];
        });

        return view('admin.marketing_campaigns.trash', compact('campaignData'));
    }
    // khôi phục
    public function restore($id)
    {
        // Tìm chiến dịch đã bị xóa (soft delete)
        $campaign = MarketingCampaign::onlyTrashed()->find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Chiến dịch không tồn tại hoặc đã được khôi phục.'
            ]);
        }

        try {
            $campaign->restore();

            return response()->json([
                'success' => true,
                'message' => 'Chiến dịch đã được khôi phục thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi khôi phục chiến dịch: ' . $e->getMessage(),
            ]);
        }
    }
    // xóa vĩnh viễn
    public function forceDelete($id)
    {
        // Tìm chiến dịch đã bị xóa (soft deleted)
        $campaign = MarketingCampaign::onlyTrashed()->find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Chiến dịch không tồn tại hoặc đã bị xóa vĩnh viễn.'
            ]);
        }

        try {
            $campaign->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Chiến dịch đã được xóa vĩnh viễn thành công.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa vĩnh viễn chiến dịch: ' . $e->getMessage(),
            ]);
        }
    }
    // xem chi tiết
    public function show($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);
        $customerGroup  = $campaign->customerGroup; 
        $coupon = $campaign->coupon; 
        // dd($coupon->toArray());
        return view('admin.marketing_campaigns.show', compact('campaign', 'customerGroup', 'coupon'));

    }
    // from sửa
    public function edit($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);
        $customerGroups = CustomerGroup::all();
        $coupons = Coupon::where('status', 'active')->get();
        $scheduledAtValue = $campaign->scheduled_at
        ? \Carbon\Carbon::parse($campaign->scheduled_at)->format('Y-m-d\TH:i')
        : null;
        return view('admin.marketing_campaigns.edit', compact(
            'campaign',
            'customerGroups',
            'coupons',
            'scheduledAtValue'
        ));
    }
    // update
    public function update(Request $request, $id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        $validated = $request->validate([
            'name'              => "required|string|max:255|unique:marketing_campaigns,name,{$id}",
            'subject'           => 'required|string|max:255',
            'content'           => 'required|string',
            'customer_group_id' => 'required|exists:customer_groups,id',
            'type'              => 'required|in:email,sms',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
        ], [
            'name.required' => 'Tên chiến dịch là bắt buộc.',
            'name.max' => 'Tên chiến dịch không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên chiến dịch này đã tồn tại, vui lòng chọn tên khác.',
        
            'subject.required' => 'Tiêu đề Email là bắt buộc.',
            'subject.max' => 'Tiêu đề Email không được vượt quá 255 ký tự.',
        
            'content.required' => 'Nội dung Email là bắt buộc.',
        
            'customer_group_id.required' => 'Vui lòng chọn nhóm khách hàng.',
            'customer_group_id.exists' => 'Nhóm khách hàng không hợp lệ.',
        
            'type.required' => 'Kênh gửi là bắt buộc.',
            'type.in' => 'Kênh gửi không hợp lệ.',
            'coupon_id.exists' => 'Mã giảm giá không hợp lệ.',
            'scheduled_at.date' => 'Thời gian lên lịch không hợp lệ.',
            'scheduled_at.after_or_equal' => 'Thời gian lên lịch không được ở quá khứ.',
        ]);
        

        // Cập nhật dữ liệu
        $campaign->name = $request->input('name');
        $campaign->email_subject = $request->input('subject');
        $campaign->email_content = $request->input('content');
        $campaign->customer_group_id = $request->input('customer_group_id');
        $campaign->channel = $request->input('type');  // nếu DB dùng cột channel cho type
        $campaign->coupon_id = $request->input('coupon_id');
        $campaign->scheduled_at = $validated['scheduled_at'] ?? null;
        // Cập nhật trạng thái nếu có
        if ($request->has('status')) {
            $campaign->status = $request->input('status');
        }

        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chiến dịch thành công',
            'campaign' => $campaign,
        ]);
    }
    // gửi chiến dịch qua email
    public function send(Request $request, $id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if (empty($campaign->email_subject) || empty($campaign->email_content)) {
            return response()->json(['error' => 'Chiến dịch chưa có tiêu đề hoặc nội dung email'], 422);
        }

        // Lấy coupon từ chiến dịch nếu có
        $coupon = null;
        if ($campaign->coupon_id) {
            $coupon = Coupon::find($campaign->coupon_id);
        }

        $emails = $campaign->customerGroup->users()->pluck('email')->toArray();

        foreach ($emails as $email) {
            Mail::to($email)->queue(new SendMarketingCampaignMail(
                $campaign->email_subject,
                $campaign->email_content,
                $campaign->name,
                $coupon ? $coupon->code : null
            ));
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response()->json(['message' => 'Chiến dịch đã được gửi đến khách hàng.']);
    }
}
