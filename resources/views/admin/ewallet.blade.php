<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>123123</h1>

    <form action="{{ route('createEwallet') }}" method="POST">
        {{ csrf_field() }}
        {{-- <input type="text" name="amount" placeholder="Amount"><br> --}}
        <select name="amount">
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="200">200</option>
            <option value="500">500</option>
            <option value="1000">1000</option>
        </select><br>
        <input type="text" name="name" placeholder="name"><br>
        <button type="submit">submit</button>
    </form>

    <table>
        <tr>
            <th>Name</th>
            <th>Amount</th>
            <th>Extra</th>
            <th>Total Value</th>
        </tr>
        @foreach($ewallet as $ewallets)
        <tr>
            <td>{{$ewallets->name}}</td>
            <td>{{$ewallets->amount}} </td>

            @if($ewallets->amount < 100)
                <td>4%</td>
                <td>{{ $ewallets->amount + (($ewallets->amount) * 0.04) }}</td>
            @elseif($ewallets->amount >= 100 && $ewallets->amount < 200)
                <td>10%</td>
                <td>{{ ($ewallets->amount + ($ewallets->amount) * 0.1) }}</td>
            @elseif($ewallets->amount >= 200 && $ewallets->amount < 500)
                <td>15%</td>
                <td>{{ ($ewallets->amount + ($ewallets->amount) * 0.15) }}</td>
            @elseif($ewallets->amount >= 500 && $ewallets->amount <= 1000)
                <td>20%</td>
                <td>{{ ($ewallets->amount + ($ewallets->amount) * 0.2) }}</td>
            @endif
        </tr>
        
        
        
       
        <br>
    @endforeach
    </table>
    
</body>
</html>