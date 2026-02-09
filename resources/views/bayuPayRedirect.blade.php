<!-- resources/views/bayupay/redirect.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Proceed to BayuPay</title>
</head>

<body>



    <p>If you are not redirected automatically, click the button below.</p>

    <form method="POST"
      action="https://bayupay-dummy.geovidia.my/checkout.php"
      id="bayupayForm">
    {{-- @dd($data) --}}
    {{-- <form method="POST" action="https://hands-on5.my/checkout.php" id="bayupayForm"> --}}
        
    {{-- <form method="POST" action="https://hands-on5.sabah.gov.my/checkout.php" id="bayupayForm"> --}}

        <input type="hidden" name="amount" value="{{ number_format($data['amount'], 2, '.', '') }}">
        <input type="hidden" name="sid" value="{{ $data['sid'] }}">
        <input type="hidden" name="rn" value="{{ $data['rn'] }}">
        <input type="hidden" name="itn" value="{{ $data['itn'] }}">
        <input type="hidden" name="bounce" value="{{ $data['bounce'] }}">


        <input type="hidden" name="co_name" value="{{ $data['co_name'] }}">
        <input type="hidden" name="co_no" value="{{ $data['rn'] }}">
        <input type="hidden" name="email" value="{{ $data['email'] }}">
        <input type="hidden" name="tel_no" value="{{ $data['tel_no'] }}">

        <button type="submit">Proceed to Payment</button>
    </form>



    <script>
        document.getElementById('bayupayForm').submit();
    </script>
    

</body>

</html>
