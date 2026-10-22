<?php
// Start a PHP session (place this at the top of the script)
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST["name"];
    $email = $_POST["email"];
    $service = $_POST["service"];
    $details = $_POST["details"];
    $date = $_POST["date"];
    $location = $_POST["location"];

    // Compose email message
    $to = "ploy4569@protonmail.com";  // Replace with your email address
    $subject = "Booking Request";
    $message = "Name: " . $name . "\n";
    $message .= "Email: " . $email . "\n";
    $message .= "Service: " . $service . "\n";
    $message .= "Details: " . $details . "\n";
    $message .= "Date: " . $date . "\n";
    $message .= "Location: " . $location . "\n";

    // Set headers
    $headers = "From: " . $email . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        $_SESSION['message'] = "Thank you for your request. A confirmation email will be sent to you shortly.";
    } else {
        $_SESSION['message'] = "Sorry, there was an error sending your booking request. Please try again.";
    }
}

// Redirect back to the contact page
header("Location: contact.php");
exit();
?>


