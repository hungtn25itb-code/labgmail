<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientsInput = trim($_POST['recipients'] ?? '');
    $emailList = array_values(array_unique(array_filter(
        array_map('trim', preg_split('/[,;\r\n]+/', $recipientsInput)),
        static fn($email) => $email !== ''
    )));
    $sender = getenv('SMTP_USERNAME');
    $password = getenv('SMTP_PASSWORD');

    if ($recipientsInput === '' || $emailList === []) {
        $message = 'Vui lòng nhập ít nhất một địa chỉ email người nhận.';
    } else {
        foreach ($emailList as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Danh sách có địa chỉ email không hợp lệ: ' . $email;
                break;
            }
        }
    }

    if ($message === '' && (!$sender || !$password)) {
        http_response_code(500);
        $message = 'Chưa cấu hình SMTP_USERNAME và SMTP_PASSWORD trong môi trường chạy PHP.';
    }

    if ($message === '') {
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
            foreach ($emailList as $email) {
                $mail->addBCC($email);
            }

            $mail->isHTML(true);
            $mail->Subject = 'Gửi thư từ PHP';
            $mail->Body = '<p>Chào bạn!</p><p>Chúc bạn an lành!</p>';
            $mail->AltBody = "Chào bạn!\n\nChúc bạn an lành!";
            $mail->send();

            $message = 'Đã gửi mail cho ' . count($emailList) . ' người nhận.';
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
    <title>Bài 3 - Gửi email cho nhiều người</title>
</head>
<body>
    <h1>Bài 3: Gửi email cho nhiều người</h1>
    <?php if ($message !== ''): ?>
        <p role="status"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <p>
            <label for="recipients">Email người nhận (ngăn cách bằng dấu phẩy, chấm phẩy hoặc xuống dòng)</label><br>
            <textarea id="recipients" name="recipients" rows="6" cols="50" required><?= htmlspecialchars($_POST['recipients'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </p>
        <button type="submit">Gửi email</button>
    </form>
</body>
</html>
