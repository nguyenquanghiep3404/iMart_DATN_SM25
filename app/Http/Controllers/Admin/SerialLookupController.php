<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\InventorySerial;
use Illuminate\Http\Request;

class SerialLookupController extends Controller
{
  /**
   * Hiển thị form tra cứu serial.
   */
  public function showForm()
  {
    return view('admin.serials.serial_lookup');
  }

  /**
   * Xử lý tra cứu serial và hiển thị kết quả.
   */
  public function lookup(Request $request)
  {
    // Validate input
    $request->validate([
      'serial_number' => 'required|string',
    ]);

    // Tìm serial với các quan hệ cần thiết
    $serial = InventorySerial::where('serial_number', $request->serial_number)
      ->with([
        'variant.product',
        'variant.primaryImage',
        'variant.attributeValues.attribute',
        'location',
        'lot.purchaseOrderItem.purchaseOrder.storeLocation',
        'stockTransferItemSerials.stockTransferItem.stockTransfer.fromLocation',
        'stockTransferItemSerials.stockTransferItem.stockTransfer.toLocation',
        'orderItemSerial.orderItem.order.storeLocation',
        'orderItemSerial.orderItem.returnItem.returnRequest',
        'orderItemSerial.orderItem.warrantyClaims',
      ])
      ->first();

    if (!$serial) {
      return back()->with('error', 'Không tìm thấy Serial/IMEI. Vui lòng kiểm tra lại.');
    }

    // Xây dựng thông tin tóm tắt
    $attributeValues = $serial->variant->attributeValues->map(function ($attrValue) {
      return $attrValue->value;
    })->implode(' ');
    $summary = [
      'name' => $serial->variant->product->name . ' ' . $attributeValues,
      'sku' => $serial->variant->sku,
      'image' => $serial->variant->getImageUrlAttribute(),
      'status' => $this->translateStatus($serial->status),
      'location' => $serial->location->name ?? 'Không xác định',
      'status_class' => $this->getStatusClass($serial->status),
    ];

    // Thu thập sự kiện vòng đời
    $events = [];

    // Nhập kho
    if ($serial->lot && $serial->lot->purchaseOrderItem && $serial->lot->purchaseOrderItem->purchaseOrder) {
      $po = $serial->lot->purchaseOrderItem->purchaseOrder;
      // Kiểm tra storeLocation trước khi truy cập name
      $storeLocationName = $po->storeLocation ? $po->storeLocation->name : 'Không xác định';
      $events[] = [
        'date' => $serial->lot->created_at->format('d/m/Y - H:i'),
        'type' => 'Nhập kho',
        'description' => "Sản phẩm đã được nhập vào <span class=\"font-semibold\">{$storeLocationName}</span> từ nhà cung cấp theo phiếu <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$po->po_code}</a>, thuộc lô <span class=\"font-semibold\">{$serial->lot->lot_code}</span>.",
        'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
        'icon_bg' => 'bg-green-200',
        'icon_color' => 'text-green-600',
        'is_latest' => false,
      ];
    }

    // Chuyển kho
    foreach ($serial->stockTransferItemSerials as $transferSerial) {
      $stockTransfer = $transferSerial->stockTransferItem->stockTransfer;
      if ($stockTransfer->shipped_at) {
        $fromLocationName = $stockTransfer->fromLocation ? $stockTransfer->fromLocation->name : 'Không xác định';
        $toLocationName = $stockTransfer->toLocation ? $stockTransfer->toLocation->name : 'Không xác định';
        $events[] = [
          'date' => $stockTransfer->shipped_at->format('d/m/Y - H:i'),
          'type' => 'Đang chuyển kho',
          'description' => "Đang chuyển từ <span class=\"font-semibold\">{$fromLocationName}</span> đến <span class=\"font-semibold\">{$toLocationName}</span> theo phiếu <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$stockTransfer->transfer_code}</a>.",
          'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h2m11-10h2m-2 2v2m0-2l-1 5h-4l-1-5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
          'icon_bg' => 'bg-blue-200',
          'icon_color' => 'text-blue-600',
          'is_latest' => false,
        ];
      }
      if ($stockTransfer->received_at) {
        $toLocationName = $stockTransfer->toLocation ? $stockTransfer->toLocation->name : 'Không xác định';
        $events[] = [
          'date' => $stockTransfer->received_at->format('d/m/Y - H:i'),
          'type' => 'Nhận hàng chuyển kho',
          'description' => "Đã nhận tại <span class=\"font-semibold\">{$toLocationName}</span> theo phiếu <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$stockTransfer->transfer_code}</a>.",
          'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10h2m11-10h2m-2 2v2m0-2l-1 5h-4l-1-5M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
          'icon_bg' => 'bg-blue-200',
          'icon_color' => 'text-blue-600',
          'is_latest' => false,
        ];
      }
    }

    // Bán hàng
    if ($serial->orderItemSerial && $serial->orderItemSerial->orderItem && $serial->orderItemSerial->orderItem->order) {
      $order = $serial->orderItemSerial->orderItem->order;
      $location = $order->storeLocation ? " tại <span class=\"font-semibold\">{$order->storeLocation->name}</span> (POS)" : '';
      $events[] = [
        'date' => $order->created_at->format('d/m/Y - H:i'),
        'type' => 'Đã bán',
        'description' => "Sản phẩm được bán trong đơn hàng <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$order->order_code}</a>{$location}.",
        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'icon_bg' => 'bg-red-200',
        'icon_color' => 'text-red-600',
        'is_latest' => true,
      ];
    }

    // Trả hàng
    if ($serial->orderItemSerial && $serial->orderItemSerial->orderItem && $serial->orderItemSerial->orderItem->returnItem) {
      $return = $serial->orderItemSerial->orderItem->returnItem->returnRequest;
      $events[] = [
        'date' => $return->created_at->format('d/m/Y - H:i'),
        'type' => 'Trả hàng',
        'description' => "Yêu cầu trả hàng <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$return->return_code}</a> ({$return->getReasonTextAttribute()}).",
        'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
        'icon_bg' => 'bg-red-200',
        'icon_color' => 'text-red-600',
        'is_latest' => false,
      ];
    }

    // Bảo hành
    if ($serial->orderItemSerial && $serial->orderItemSerial->orderItem) {
      foreach ($serial->orderItemSerial->orderItem->warrantyClaims as $warranty) {
        $events[] = [
          'date' => $warranty->created_at->format('d/m/Y - H:i'),
          'type' => 'Bảo hành',
          'description' => "Yêu cầu bảo hành <a href=\"#\" class=\"font-medium text-indigo-600 hover:underline\">{$warranty->claim_code}</a> ({$warranty->reported_defect}).",
          'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
          'icon_bg' => 'bg-yellow-200',
          'icon_color' => 'text-yellow-600',
          'is_latest' => false,
        ];
      }
    }

    // Sắp xếp sự kiện theo ngày (mới nhất trước)
    // Sắp xếp sự kiện theo ngày (mới nhất trước)
    usort($events, function ($a, $b) {
      // So sánh các chuỗi ngày tháng theo đúng định dạng Y-m-d H:i:s
      $dateA = \DateTime::createFromFormat('d/m/Y - H:i', $a['date']);
      $dateB = \DateTime::createFromFormat('d/m/Y - H:i', $b['date']);

      if ($dateA == $dateB) {
        return 0;
      }
      // Nếu dateB lớn hơn dateA (mới hơn), trả về số dương để đưa dateB lên trước
      return $dateB > $dateA ? 1 : -1;
    });

    // Gán lại is_latest cho sự kiện mới nhất
    if (!empty($events)) {
      foreach ($events as &$event) {
        $event['is_latest'] = false;
      }
      $events[0]['is_latest'] = true;
    }
    // Dữ liệu gửi đến view
    $serialData = [
      'summary' => $summary,
      'events' => $events,
    ];

    return view('admin.serials.serial_lookup', [
      'serialData' => $serialData,
      'serial_number' => $request->serial_number,
    ]);
  }

  /**
   * Dịch trạng thái sang tiếng Việt.
   */
  private function translateStatus($status)
  {
    $statuses = [
      'available'   => 'Sẵn sàng bán',
      'transferred' => 'Đang chuyển kho',
      'sold'        => 'Đã bán cho khách',
      'defective'   => 'Hàng lỗi / hỏng',
      'returned'    => 'Khách đã trả hàng',
    ];

    return $statuses[$status] ?? $status;
  }

  /**
   * Lấy class Tailwind cho trạng thái.
   */
  private function getStatusClass($status)
  {
    $classes = [
      'available' => 'bg-green-100 text-green-800',
      'transferred' => 'bg-blue-100 text-blue-800',
      'sold' => 'bg-red-100 text-red-800',
      'defective' => 'bg-yellow-100 text-yellow-800',
      'returned' => 'bg-gray-100 text-gray-800',
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
  }
}
