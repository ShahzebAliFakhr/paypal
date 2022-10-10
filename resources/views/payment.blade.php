<form action="{{ url('payment/charge') }}" method="post">
    <input type="text" name="amount" />
    {{ csrf_field() }}
    <input type="submit" value="Pay Now">
</form>