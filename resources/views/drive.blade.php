<form action="{{ url('google/upload') }}" method="post" enctype="multipart/form-data">
    <input type="file" name="file" />
    {{ csrf_field() }}
    <input type="submit" value="Upload">
</form>