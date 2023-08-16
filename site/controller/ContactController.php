<?php



class ContactController
{

    // Hiển thị form liên hệ
    function form()
    {
        require ABSPATH_SITE .'view/contact/form.php';
    }

    // Gởi mail đến chủ cửa hàng
    function sendEmail()
    {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $content = $_POST['content'];

        $emailService = new EmailService();
        $subject = 'Godashop: Liên hệ';
        $to = SHOP_OWNER;
        $domain = get_domain();
        $content = "
        Chào chủ cửa hàng,<br>
        Dưới đây là thông tin khách hàng liên hệ:<br>
        Họ và tên: $fullname,<br>
        Email: $email,<br>
        Mobile: $mobile, <br>
        Nội dung: $content,<br>
        Được gởi từ trang web: $domain
        ";
        if ($emailService->send($to, $subject, $content)) {
            echo 'Đã gởi mail thành công';
            return;
        }
        echo $emailService->error;
    }
}
