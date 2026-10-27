<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = trim($_POST['email'] ?? '');
    $sender = getenv('SMTP_USERNAME');
    $password = getenv('SMTP_PASSWORD');

    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $message = 'Vui lòng nhập địa chỉ Gmail người nhận hợp lệ.';
    } elseif (!$sender || !$password) {
        http_response_code(500);
        $message = 'Chưa cấu hình SMTP_USERNAME và SMTP_PASSWORD trong môi trường chạy PHP.';
    } else {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $sender;
            $mail->Password = $password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($sender, 'Người gửi');
            $mail->addAddress($recipient);
            $mail->isHTML(true);
            $mail->Subject = 'Gửi thư từ PHP';
            $mail->Body = '<p>Chào bạn!</p><p>Chúc bạn an lành!</p>';
            $mail->AltBody = "Chào bạn!\n\nChúc bạn an lành!";

            $attachment = __DIR__ . '/4.jpg';
            if (is_file($attachment)) {
                $mail->addAttachment($attachment, 'picture.jpg');
            }

            $mail->send();
            $message = 'Đã gửi mail xong.';
        } catch (Exception $exception) {
            error_log('Không gửi được email: ' . $mail->ErrorInfo);
            http_response_code(500);
            $message = 'Mail không gửi được. Vui lòng kiểm tra cấu hình SMTP.';
        }
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bài 1 - Gửi email</title>
</head>
<body>
    <h1>Bài 1: Gửi email mẫu</h1>
    <?php if ($message !== ''): ?>
        <p role="status"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <p>
            <label for="email">Gmail người nhận</label><br>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        </p>
        <button type="submit">Gửi email</button>
    </form>
</body>
</html>
