<form action="/test" method="post" enctype="multipart/form-data" >
    {{ csrf_field() }}
    <input type="file" name="avatar" />
    <input type="hidden" name="cropData" value="[1555,680,0,0]" />
    <input type="submit" value="avatar" />
</form>
