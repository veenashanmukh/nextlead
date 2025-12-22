<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include 'connection.php';

// Check if the user is logged in as a voter
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Voter') {
    header("Location: login.php");
    exit;
}

// Get form values
$voter_id = $_POST['voter_id'] ?? null;
$candidate_id = $_POST['candidate_id'] ?? null;
$election_id = $_POST['election_id'] ?? null;

// Basic validation
if (!$voter_id || !$candidate_id || !$election_id) {
    $_SESSION['message'] = "Invalid voting data. Please try again.";
    header("Location: voter.php");
    exit;
}

// Check if the voter has already voted
$check_vote_sql = "SELECT * FROM votes WHERE voter_id = ? AND election_id = ?";
$stmt_check_vote = $conn->prepare($check_vote_sql);
$stmt_check_vote->bind_param("ii", $voter_id, $election_id);
$stmt_check_vote->execute();
$vote_result = $stmt_check_vote->get_result();

if ($vote_result->num_rows > 0) {
    $_SESSION['message'] = "You have already voted. Thank you for your participation.";
    header("Location: voter.php");
    exit;
}
$stmt_check_vote->close();

// Insert the vote
$insert_vote_sql = "INSERT INTO votes (voter_id, candidate_id, election_id) VALUES (?, ?, ?)";
$stmt_insert_vote = $conn->prepare($insert_vote_sql);
$stmt_insert_vote->bind_param("iii", $voter_id, $candidate_id, $election_id);

if ($stmt_insert_vote->execute()) {
    // Fetch voter's email
    $email_query = $conn->prepare("SELECT name, email FROM voters WHERE voter_id = ?");
    $email_query->bind_param("i", $voter_id);
    $email_query->execute();
    $email_query->bind_result($voter_name, $voter_email);
    $email_query->fetch();
    $email_query->close();

    // Load PHPMailer (manual installation)
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';
    require 'phpmailer/src/Exception.php';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'veenasb2006@gmail.com';
        $mail->Password = 'pttfbxmmcgjwoumh'; // Use your Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // ❗Disable SSL certificate verification (for local testing only)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        // Recipients
        $mail->setFrom('veenasb2006@gmail.com', 'Online Voting System');
        $mail->addAddress($voter_email, $voter_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Vote Confirmation';
        $mail->Body = "Hello <strong>$voter_name</strong>,<br><br>Thank you for casting your vote in Election ID <strong>$election_id</strong>.<br><br>Regards,<br>Online Voting System";

        $mail->send();
        $_SESSION['message'] = "Thank you for voting! Your vote has been recorded and a confirmation email has been sent.";
    } catch (Exception $e) {
        $_SESSION['message'] = "Your vote was recorded, but the confirmation email could not be sent. Error: {$mail->ErrorInfo}";
    }
} else {
    $_SESSION['message'] = "There was an error while recording your vote. Please try again.";
}

$stmt_insert_vote->close();
$conn->close();

// Redirect
header("Location: voter.php");
exit;
