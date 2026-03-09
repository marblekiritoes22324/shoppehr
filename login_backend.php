<?php
// login_backend.php
require 'db.php';
session_start();
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $role = $data['role'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($role === 'employee') {
                require 'PHPMailer-master/src/PHPMailer.php';
                require 'PHPMailer-master/src/SMTP.php';
                require 'PHPMailer-master/src/Exception.php';
                
                $otp = rand(100000, 999999);
                $_SESSION['session_otp'] = $otp;
                $_SESSION['session_email'] = $user['email'];
                $_SESSION['pending_user'] = [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                $_SESSION['pending_user_id'] = $user['id'];
                
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'cajotealfredourgel60@gmail.com'; 
                    $mail->Password = 'eednrwawvsoxyvhh';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;
            
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
            
                    $mail->setFrom('cajotealfredourgel60@gmail.com', 'Security Log System');
                    $mail->addAddress($email);
            
                    $mail->Subject = 'Your Employee OTP Code';
                    $mail->Body = "Your One-Time Password is: " . $otp . "\n\nPlease do not share this code with anyone.";
            
                    $mail->send();
                    echo json_encode(['success' => true, 'require_otp' => true]);
                    exit;
                } catch (Exception $e) {
                    error_log("Failed to send OTP email: " . $mail->ErrorInfo);
                    echo json_encode(['success' => false, 'message' => 'Failed to send OTP email.']);
                    exit;
                }
            }

            // Update last activity
            $updateStmt = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            echo json_encode([
                'success' => true,
                'user' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
