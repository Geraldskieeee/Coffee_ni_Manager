<?php
session_start();

// SECURITY CHECK: Kick out anyone who isn't logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: index.php?status=unauthorized#contact");
    exit();
}

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize basic inputs
    $inquiry_type = htmlspecialchars(strip_tags($_POST['inquiry_type'] ?? 'General Inquiry'));
    $name = htmlspecialchars(strip_tags($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(strip_tags($_POST['message'] ?? 'None'));

    // Sanitize the new specific Delivery inputs
    $coffee_choice = htmlspecialchars(strip_tags($_POST['coffee_choice'] ?? 'N/A'));
    $sugar_level = htmlspecialchars(strip_tags($_POST['sugar_level'] ?? 'N/A'));
    $cash_amount = htmlspecialchars(strip_tags($_POST['cash_amount'] ?? 'N/A'));
    $phone = htmlspecialchars(strip_tags($_POST['phone'] ?? 'N/A'));
    $street = htmlspecialchars(strip_tags($_POST['street'] ?? 'N/A'));
    $barangay = htmlspecialchars(strip_tags($_POST['barangay'] ?? 'N/A'));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $mail = new PHPMailer(true);

    try {
        // --- 1. SMTP Server Settings ---
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';             
        $mail->SMTPAuth   = true;                                   
        
        $mail->Username   = 'villegasgerald22@gmail.com'; 
        $mail->Password   = 'hfpyhgcsmcsdbbil'; 
                                       
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
        $mail->Port       = 587;                                    

        // --- 2. Recipients ---
        $mail->setFrom('villegasgerald22@gmail.com', 'Coffee Ni Manager System');
        $mail->addAddress('villegasgerald22@gmail.com', 'Coffee Shop Admin'); 
        $mail->addReplyTo($email, $name);

        // --- 3. Content Formatting ---
        $mail->isHTML(true);                                  
        
        // Change the subject line depending on if it's an order or a message
        if ($inquiry_type === 'Delivery Order') {
            $mail->Subject = "☕ New Delivery Order from $name";
        } else {
            $mail->Subject = "New Website Inquiry from $name";
        }
        
        // Only show Order & Delivery details in the email if they actually placed an order
        $extraDetails = "";
        if ($inquiry_type === 'Delivery Order') {
            $extraDetails = "
                <h4 style='color: #d2a65a; margin-bottom: 5px; margin-top: 15px;'>Order Details</h4>
                <p style='margin: 0;'><strong>Coffee Choice:</strong> {$coffee_choice}</p>
                <p style='margin: 0;'><strong>Sugar Level:</strong> {$sugar_level}</p>
                <p style='margin: 0;'><strong>Payment Tendered:</strong> ₱{$cash_amount}</p>
                
                <h4 style='color: #d2a65a; margin-bottom: 5px; margin-top: 15px;'>Delivery Information</h4>
                <p style='margin: 0;'><strong>Phone Number:</strong> {$phone}</p>
                <p style='margin: 0;'><strong>Address:</strong> {$street}, Brgy. {$barangay}, San Juan, Ilocos Sur</p>
                <hr style='border: 1px solid #eee; margin: 15px 0;'>
            ";
        }

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h3 style='color: #161616; border-bottom: 2px solid #f5deb3; padding-bottom: 10px;'>{$inquiry_type}</h3>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                {$extraDetails}
                <p><strong>Notes / Message:</strong><br/>" . nl2br($message) . "</p>
            </div>
        ";

        $mail->send();
        
        header("Location: index.php?status=success");
        exit();
        
    } catch (Exception $e) {
        header("Location: index.php?status=error");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>