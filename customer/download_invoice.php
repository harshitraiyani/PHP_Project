<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
require_once __DIR__ . '/../vendor/autoload.php'; // composer install dompdf/dompdf

use Dompdf\Dompdf;

$invoiceId = intval($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT i.*, b.booking_date, b.price, b.extra_charge, b.extra_charge_note,
           u.name AS customer_name, u.email, u.phone,
           br.name AS brand_name, m.name AS model_name, v.registration_no, st.name AS service_name
    FROM invoices i
    JOIN bookings b ON i.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    JOIN service_types st ON b.service_type_id = st.id
    WHERE i.id = ? AND (b.user_id = ? OR ? = 'admin')
");
$role = $_SESSION['role'];
$stmt->bind_param("iis", $invoiceId, $userId, $role);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice) {
    die("Invoice not found or access denied.");
}

$html = "
<html>
<head>
<meta charset='UTF-8'>
<style>
  body { font-family: 'DejaVu Sans', sans-serif; color: #1B2430; font-size: 13px; }
  .header { background: #1B3A5C; color: #fff; padding: 20px; }
  .header h1 { margin: 0; font-size: 22px; }
  .header p { margin: 2px 0; font-size: 12px; }
  .section { padding: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background: #f4f6f8; }
  .total-row td { font-weight: bold; background: #f4f6f8; }
  .footer { text-align: center; color: #888; font-size: 11px; margin-top: 30px; }
  .badge { padding: 4px 10px; border-radius: 4px; background: #2F9E5B; color: #fff; font-size: 11px; }
</style>
</head>
<body>
  <div class='header'>
    <h1>Car Service</h1>
    <p>Service Invoice</p>
  </div>
  <div class='section'>
    <table style='border:none;'>
      <tr style='border:none;'>
        <td style='border:none; width:50%;'>
          <strong>Invoice No:</strong> {$invoice['invoice_no']}<br>
          <strong>Date:</strong> " . date('d M Y', strtotime($invoice['created_at'])) . "
        </td>
        <td style='border:none; width:50%;'>
          <strong>Customer:</strong> " . htmlspecialchars($invoice['customer_name']) . "<br>
          <strong>Email:</strong> " . htmlspecialchars($invoice['email']) . "<br>
          <strong>Phone:</strong> " . htmlspecialchars($invoice['phone']) . "
        </td>
      </tr>
    </table>

    <table>
      <thead>
        <tr>
          <th>Description</th>
          <th>Vehicle</th>
          <th>Service Date</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>" . htmlspecialchars($invoice['service_name']) . "</td>
          <td>" . htmlspecialchars($invoice['brand_name'] . ' ' . $invoice['model_name'] . ' (' . $invoice['registration_no'] . ')') . "</td>
          <td>" . date('d M Y', strtotime($invoice['booking_date'])) . "</td>
          <td>Rs. " . number_format($invoice['price'], 2) . "</td>
        </tr>" . ($invoice['extra_charge'] > 0 ? "
        <tr>
          <td colspan='2'>Additional work" . ($invoice['extra_charge_note'] ? ' — ' . htmlspecialchars($invoice['extra_charge_note']) : '') . "</td>
          <td></td>
          <td>Rs. " . number_format($invoice['extra_charge'], 2) . "</td>
        </tr>" : "") . "
        <tr>
          <td colspan='3' style='text-align:right;'>Tax</td>
          <td>Rs. " . number_format($invoice['tax'], 2) . "</td>
        </tr>
        <tr class='total-row'>
          <td colspan='3' style='text-align:right;'>Total Amount</td>
          <td>Rs. " . number_format($invoice['total'], 2) . "</td>
        </tr>
      </tbody>
    </table>

    <div class='footer'>
      Thank you for choosing Vehicle Service Portal!<br>
      This is a system-generated invoice.
    </div>
  </div>
</body>
</html>
";

$dompdf = new Dompdf();
$dompdf->set_option('isHtml5ParserEnabled', true);
$dompdf->set_option('isRemoteEnabled', true);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($invoice['invoice_no'] . '.pdf', ['Attachment' => true]);
