<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Grabmaid Payment</title>
</head>
<body>
    @if ($data['hasRequiredFiled'] === true)
        @if ($data['bookingFound'] === true)
        <form id="ePayment" action="https://payment.ipay88.com.my/epayment/entry.asp" method="post" name="ePayment">
            <input type="hidden" name="MerchantCode" value="{{ env('IPAY_MERCHANT_CODE') }}" />
            <input type="hidden" name="PaymentId" value="{{ $data['paymentId'] }}" />
            <input type="hidden" name="RefNo" value="{{ $data['refNo'] }}" />
            <input type="hidden" name="Amount" value="{{ number_format($data['amount'], 2, '.', ',') }}" />
            <input type="hidden" name="Currency" value="{{ $data['currency'] }}" />
            <input type="hidden" name="ProdDesc" value="Grabmaid Booking Payment" />
            <input type="hidden" name="UserName" value="{{ $data['username'] }}" />
            <input type="hidden" name="UserEmail" value="{{ $data['email'] }}" />
            <input type="hidden" name="UserContact" value="{{ $data['contact'] }}" />
            <input type="hidden" name="Remark" value="" />
            <input type="hidden" name="Lang" value="UTF-8" />
            <input type="hidden" name="Signature" value="{{ $data['signature'] }}" />
            <input type="hidden" name="SignatureType" value="SHA256" />
            <input type="hidden" name="ResponseURL" value="{{ $data['responseUrl'] }}" />
            <input type="hidden" name="BackendURL" value="{{ $data['backendUrl'] }}" />
            <input style="display:block" type="submit" value="Proceed with Payment" name="Submit">
        </form>
        @else
        <p>Error! Could not find this booking for payment.</p>
        <a href="{{ route('payment.back-to-app') }}">Return to Application and contact us</a>
        @endif
    @else
    <p>Missing data for payment process</p>
    <a href="{{ route('payment.back-to-app') }}">Return to Application</a>
    @endif


</body>
<script language="javascript">
    @if ($data['hasRequiredFiled'] === true)
    window.onload = function() {
        var form = document.getElementById('ePayment');
        if (form) {
            form.submit();
        }
    };
    @endif
</script>
</html>
