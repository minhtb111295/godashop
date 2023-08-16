<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CustomerController
{
    //hiển thị thông tin tài khoản
    function show()
    {
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        require ABSPATH_SITE .'view/customer/show.php';
    }

    // cập nhật thông tin tài khoản
    function updateInfo()
    {
        $fullname = $_POST['fullname'];
        $mobile = $_POST['mobile'];

        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        // cập nhật object
        $customer->setName($fullname);
        $customer->setMobile($mobile);

        //Kiểm tra mật khẩu hiện tại
        $current_password = $_POST['current_password'];
        $new_password = $_POST['password'];
        if (
            $current_password &&
            $new_password &&
            !password_verify($current_password, $customer->getPassword())
        ) {
            $_SESSION['error'] = 'Lỗi: mật khẩu hiện tại không đúng';
            header('location: /index.php?c=customer&a=show');
            exit;
        }

        if ($current_password && $new_password) {
            //mã hóa mật khẩu mới và cập nhật object
            $encode_new_password = password_hash($new_password, PASSWORD_BCRYPT);
            $customer->setPassword($encode_new_password);
        }


        // update object này xuống db
        if ($customerRepository->update($customer)) {
            $_SESSION['name'] = $fullname;
            $_SESSION['success'] = "Đã cập nhật thông tin tài khoản thành công";
            header('location: /index.php?c=customer&a=show');
            exit;
        }
        $_SESSION['error'] = $customerRepository->getError();
        header('location: /index.php?c=customer&a=show');
        exit;
    }

    // Hiển thị địa chỉ giao hàng mặc định
    function shippingDefault()
    {
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        require ABSPATH_SITE .'view/customer/shippingDefault.php';
    }

    function updateShippingDefault()
    {
        $email = $_SESSION['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        // Cập nhật giá trị mới vào object customer
        $customer->setShippingName($_POST['fullname']);
        $customer->setShippingMobile($_POST['mobile']);
        $customer->setWardId($_POST['ward']);
        $customer->setHousenumberStreet($_POST['address']);
        // Lưu xuống database
        if ($customerRepository->update($customer)) {
            // update session
            $_SESSION['success'] = 'Đã cập nhật địa chỉ giao hàng mặc định thành công';
            header('location: /index.php?c=customer&a=shippingDefault');
            exit;
        }
        $_SESSION['error'] = $customerRepository->getError();
        header('location: /index.php?c=customer&a=shippingDefault');
    }

    // Hiển thị danh sách đơn hàn
    function orders()
    {
        $email = $_SESSION['email'];
        $orderRepository = new OrderRepository();
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        $orders = $orderRepository->getByCustomerId($customer->getId());
        require ABSPATH_SITE .'view/customer/orders.php';
    }

    // Hiển thị chi tiết đơn hàng
    function orderDetail()
    {
        $orderId = $_GET['id'];
        $orderRepository = new OrderRepository();
        $order = $orderRepository->find($orderId);
        require ABSPATH_SITE .'view/customer/orderDetail.php';
    }

    function notExistingEmail()
    {
        // nếu email tồn tại trong hệ thống thì echo false;
        //ngược lại là echo true
        $email = $_GET['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        if (!empty($customer)) {
            echo 'false';
            return;
        }
        echo 'true';
    }

    // Tạo tài khoản người dùng
    function register()
    {

        //check google recaptcha
        $gRecaptchaResponse = $_POST['g-recaptcha-response'];
        $secret = GOOGLE_RECAPTCHA_SECRET;
        $recaptcha = new \ReCaptcha\ReCaptcha($secret);
        $resp = $recaptcha->setExpectedHostname('godashop.com')
            ->verify($gRecaptchaResponse, '127.0.0.1');
        if (!$resp->isSuccess()) {
            // !Verified!
            $errors = $resp->getErrorCodes();
            // implode là nối các phần tử trong array lại thành chuỗi
            $error = implode('<br>', $errors);
            $_SESSION['error'] = 'Error: ' . $error;
            header('location:/');
            exit;
        }
        $data["name"] = $_POST['fullname'];
        $data["password"]  = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $data["mobile"] = $_POST['mobile'];
        $data["email"]  = $_POST['email'];
        $data["login_by"] = 'form';
        $data["shipping_name"] = $_POST['fullname'];
        $data["shipping_mobile"] = $_POST['mobile'];
        $data["ward_id"] = NULL;
        $data["is_active"] = 0;
        $data["housenumber_street"] = '';

        $customerRepository = new CustomerRepository();
        $customerRepository->save($data);
        //Gởi mail active account đến người đăng ký tạo tài khoản
        $emailService = new EmailService();
        $to = $_POST['email'];
        $subject = 'Godashop - Verify your email';
        $payload = [
            'email' => $to
        ];
        $token = JWT::encode($payload, JWT_KEY, 'HS256');

        $linkActive = get_domain() . '/index.php?c=customer&a=active&token=' . $token;
        $name = $data["name"];
        $website = get_domain();
        $content = "
         Dear $name,<br>
         Vui lòng click vào link bên dưới để active account<br>
         <a href='$linkActive'>Active Account</a><br>
         -----------<br>
         Được gởi từ $website
         ";

        $emailService->send($to, $subject, $content);

        $_SESSION['success'] = 'Đã đăng ký thành công. Vui lòng kích hoạt tài khoản';
        header('location:/');
    }

    function active()
    {
        $token = $_GET['token'];
        try {
            $decoded = JWT::decode($token, new Key(JWT_KEY, 'HS256'));
            $email = $decoded->email;
            $customerRepository = new CustomerRepository();
            $customer = $customerRepository->findEmail($email);
            $customer->setIsActive(1);
            // Lưu xuống database
            if ($customerRepository->update($customer)) {
                // update session
                // Đăng nhập thành công
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $customer->getName();
                // tạm thời cho về trang chủ sau khi đăng nhập thành công
                header('location: /index.php?c=customer&a=show');
                $_SESSION['success'] = 'Đã kích hoạt tài khoản thành công';
                exit;
            }
            $_SESSION['error'] = $customerRepository->getError();
            exit;
        } catch (Exception $e) {
            echo 'You try hack!';
        }
    }

    function forgotPassword()
    {
        $email = $_POST['email'];
        $customerRepository = new CustomerRepository();
        $customer = $customerRepository->findEmail($email);
        if (empty($customer)) {
            $_SESSION['error'] = 'Email này không tồn tại';
            header('location:/');
            exit;
        }

        //Gởi mail active account đến người đăng ký tạo tài khoản
        $emailService = new EmailService();
        $to = $email;
        $subject = 'Godashop - Reset your email';
        $payload = [
            'email' => $to
        ];
        $token = JWT::encode($payload, JWT_KEY, 'HS256');

        $linkReset = get_domain() . '/index.php?c=customer&a=reset&token=' . $token;
        $name = $customer->getName();
        $website = get_domain();
        $content = "
         Dear $name,<br>
         Vui lòng click vào link bên dưới để reset account<br>
         <a href='$linkReset'>Reset Account</a><br>
         -----------<br>
         Được gởi từ $website
         ";

        $emailService->send($to, $subject, $content);

        $_SESSION['success'] = 'Đã gởi emai thành công. Vui lòng check email để reset password';
        header('location:/');
    }

    function reset()
    {
        $token = $_GET['token'];
        require ABSPATH_SITE .'view/customer/reset.php';
    }

    function updatePassword()
    {
        $token = $_POST['token'];
        try {
            $decoded = JWT::decode($token, new Key(JWT_KEY, 'HS256'));
            $email = $decoded->email;
            $customerRepository = new CustomerRepository();
            $customer = $customerRepository->findEmail($email);
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $customer->setPassword($password);
            // Lưu xuống database
            if ($customerRepository->update($customer)) {
                // update session
                // Đăng nhập thành công
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $customer->getName();
                // tạm thời cho về trang chủ sau khi đăng nhập thành công
                header('location: /index.php?c=customer&a=show');
                $_SESSION['success'] = 'Đã reset mật khẩu thành công';
                exit;
            }
            $_SESSION['error'] = $customerRepository->getError();
            exit;
        } catch (Exception $e) {
            echo 'You try hack!';
        }
    }

    function test1()
    {
        $key = 'example_key';
        $payload = [
            'emai' => 'abc@gmail.com'
        ];
        $jwt = JWT::encode($payload, $key, 'HS256');
        echo $jwt;
    }

    function test2()
    {
        $key = 'example_key';
        $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpIjoiYWJjQGdtYWlsLmNvbSJ9.qn56rL6TIwtn31mbnEJqKyi8fK2iBHBXzSWF21RzlSw';
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        var_dump($decoded);
    }
}