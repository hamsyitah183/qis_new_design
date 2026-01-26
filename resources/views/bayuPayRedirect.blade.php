<!-- resources/views/bayupay/redirect.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proceed to BayuPay</title>
</head>
<body>
    {{-- @dd($data) --}}


<p>If you are not redirected automatically, click the button below.</p>

<form method="POST"
      action="https://bayupay-dummy.geovidia.my/checkout.php"
      id="bayupayForm">
{{-- <form method="POST"
      action="http://10.71.97.95/checkout.php"
      id="bayupayForm"> --}}

    @foreach ($data as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach

    <input type="hidden" name="token" value="test-api">

    <button type="submit">Proceed to Payment</button>
</form>

<script>
    setTimeout(function () {
        document.getElementById('bayupayForm').submit();
    }, 500);
</script>

</body>
</html>