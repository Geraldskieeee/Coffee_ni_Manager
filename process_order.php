<?php
session_start();

// Use Composer's autoloader!
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    echo "Error: You must be logged in to place an order.";
    exit();
}

// 2. Receive the JSON payload from Javascript
$json = file_get_contents('php://input');
$payload = json_decode($json, true);

$cart = $payload['cart'] ?? [];
$delivery = $payload['delivery'] ?? [];

if (count($cart) == 0) {
    echo "Error: Your cart is empty.";
    exit();
}

$userEmail = $_SESSION['user_email'];

// 3. Build the HTML Email Receipt
$orderHTML = "
    <div style='font-family: Arial, sans-serif; padding: 20px; color: #333; max-w: 600px; margin: auto;'>
        <h2 style='color: #161616; border-bottom: 2px solid #f5deb3; padding-bottom: 10px;'>New Delivery Order</h2>
        
        <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
            <h3 style='margin-top: 0; color: #d2a65a;'>Delivery Information</h3>
            <p style='margin: 5px 0;'><strong>Name:</strong> " . htmlspecialchars($delivery['name']) . "</p>
            <p style='margin: 5px 0;'><strong>Phone:</strong> " . htmlspecialchars($delivery['phone']) . "</p>
            <p style='margin: 5px 0;'><strong>Address:</strong> " . nl2br(htmlspecialchars($delivery['address'])) . "</p>
            <p style='margin: 5px 0;'><strong>Notes:</strong> " . nl2br(htmlspecialchars($delivery['notes'])) . "</p>
            <p style='margin: 5px 0;'><strong>Account Email:</strong> {$userEmail}</p>
        </div>

        <h3 style='color: #d2a65a;'>Order Details</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr style='background-color: #f4f4f4;'>
                <th style='border: 1px solid #ddd; padding: 10px; text-align: left;'>Item</th>
                <th style='border: 1px solid #ddd; padding: 10px; text-align: center;'>Qty</th>
                <th style='border: 1px solid #ddd; padding: 10px; text-align: right;'>Price</th>
                <th style='border: 1px solid #ddd; padding: 10px; text-align: right;'>Subtotal</th>
            </tr>
";

$total = 0;
foreach ($cart as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $orderHTML .= "
        <tr>
            <td style='border: 1px solid #ddd; padding: 10px;'>" . htmlspecialchars($item['name']) . "</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: center;'>{$item['quantity']}</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>₱{$item['price']}</td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right;'>₱{$subtotal}</td>
        </tr>
    ";
}

$orderHTML .= "
        <tr>
            <td colspan='3' style='border: 1px solid #ddd; padding: 10px; text-align: right;'><strong>Total Amount Due:</strong></td>
            <td style='border: 1px solid #ddd; padding: 10px; text-align: right; color: #d2a65a; font-size: 18px;'><strong>₱{$total}</strong></td>
        </tr>
    </table>
    </div>
";

// 4. Send via PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.gmail.com';             
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = 'villegasgerald22@gmail.com'; 
    $mail->Password   = 'hfpyhgcsmcsdbbil';                             
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
    $mail->Port       = 587;                                    

    // Sender and Recipient
    $mail->setFrom('villegasgerald22@gmail.com', 'Coffee Ni Manager Orders');
    $mail->addAddress('villegasgerald22@gmail.com', 'Admin'); 
    
    // Send a confirmation receipt back to the user's logged-in email!
    $mail->addAddress($userEmail, $delivery['name']); 

    $mail->isHTML(true);                                  
    $mail->Subject = "Coffee Order #". rand(1000, 9999) ." - Delivery for " . htmlspecialchars($delivery['name']);
    $mail->Body    = $orderHTML;

    $mail->send();
    echo "Success: Your order has been placed and is being prepared!";
} catch (Exception $e) {
    echo "Error: Order could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>