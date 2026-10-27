<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = trim($_POST['email'] ?? '');
    $subject = trim($_POST['tieude'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $sender = getenv('SMTP_USERNAME');
    $password = getenv('SMTP_PASSWORD');

    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $message = 'Vui lòng nhập địa chỉ email hợp lệ.';
    } elseif ($subject === '' || $content === '') {
        $message = 'Vui lòng nhập tiêu đề và nội dung email.';
    } elseif (!$sender || !$password) {
        http_response_code(500);
        $message = 'Chưa cấu hình SMTP_USERNAME và SMTP_PASSWORD trong môi trường chạy PHP.';
    } elseif (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE && $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Tệp đính kèm tải lên không thành công.';
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

            $mail->setFrom($sender, 'Hung');
            $mail->addAddress($recipient, 'Bạn');
            $mail->addAddress('hungtran13022007@gmail.com', 'hungtran');
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = '<h3>Xin chào ' . htmlspecialchars($recipient, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h3><p>'
                . nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . '</p>';
            $mail->AltBody = "Xin chào {$recipient}\n\n{$content}";

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $mail->addAttachment($_FILES['file']['tmp_name'], basename($_FILES['file']['name']));
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
    <title>Gửi email</title>
</head>
<body>
    <h1>Gửi email</h1>
    <?php if ($message !== ''): ?>
        <p role="status"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
    <form action="" method="post" enctype="multipart/form-data">
        <p>
            <label for="email">Email người nhận</label><br>
            <input id="email" name="email" type="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        </p>
        <p>
            <label for="tieude">Tiêu đề</label><br>
            <input id="tieude" name="tieude" type="text" required value="<?= htmlspecialchars($_POST['tieude'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        </p>
        <p>
            <label for="content">Nội dung</label><br>
            <textarea id="content" name="content" required><?= htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </p>
        <p>
            <label for="file">Tệp đính kèm (không bắt buộc)</label><br>
            <input id="file" name="file" type="file">
        </p>
        <button type="submit">Gửi</button>
    </form>
</body>
</html>
