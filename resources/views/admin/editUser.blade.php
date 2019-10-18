<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
</head>
<body>
    <h1>123123</h1>

<form action="{{ route('editUser') }}" method="POST">
    {{ csrf_field() }}
    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone No</th>
            <th>Last Login</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
        @foreach ($users as $user)
        <tr>
            <td>{{($user->name)}}</td>
            <td>{{($user->email)}}</td>
            <td>{{($user->mobile_no)}}</td>
            <td>{{($user->updated_at)}}</td>
            <td>{{($user->created)}}</td>
        </tr>
        @endforeach
    </table>
    <div>
        {{ $users->links() }}
    </div>
    
   

    <button type="submit">Submit</button>
</form>

<form action="{{ route('deleteUser') }}" method="POST">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="146">
    <button type="submit">submit</button>
</form>
</body>
</html>