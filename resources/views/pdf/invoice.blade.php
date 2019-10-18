<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Grabmaid Receipt {{ $title ?? '' }}</title>
</head>
<body style="margin:0 auto; font-family: Verdana, Arial, sans-serif; font-size: 12px; background-color: #fff;">
    <table style="background-color: #fff" align="center" border="0" cellpadding="0" cellspacing="0" width="500">
        <tr>
            <td align="center" colspan="2" style="padding: 15px;">
                <img src="{{ asset('images/gm-logo-color.png') }}" width="120" alt="Grabmaid">
            </td>
        </tr>
        <tr>
            <td align="left" style="width:35%; padding: 15px;">
                <h4 style="margin-top:0px">{{ $customer->name ?? '' }} <br> {{ $customer->email ?? '' }}</h4>
                <p>
                    <span>{{ $address->address_line ?? '' }}</span>
                    <br />
                    <span>{{ $address->region ?? '' }}</span>,
                    <br />
                    <span>{{ $address->postcode ?? '' }}</span>, <span>{{ $address->city ?? '' }}</span>
                    <br />
                    <span>{{ $address->state ?? '' }}</span>
                </p>
            </td>

            <td align="right" style="width:35%; padding: 15px;">
                <h4 style="margin-top:0px">{{ $company->name }} by <br /> Grab Maid Tech Sdn Bhd</h4>
                <p>
                    Block A909, Paradesa Rustica, Persiaran Meranti, 52200 Bandar Sri Damansara, PJU 9, Petaling Jaya, Selangor.
                    <!-- TODO change to vendor address -->
                </p>
            </td>
        </tr>
        <tr>
            <td align="left" style="padding-left: 15px; padding-bottom: 15px;">
                <span>Booking Number: {{ $booking->booking_number }}</span>
            </td>
            <td align="right" style="padding-right: 15px; padding-bottom: 15px;">
                <span>Date: {{ $booking->created_at->format('d-m-Y') }}</span>
            </td>
        </tr>
    </table>
    <table style="background-color: #fff" align="center" border="0" cellpadding="0" cellspacing="0" width="500">
        <tr>
            <td align="center" colspan="2">
                <h2 style="font-weight: 700">Receipt</h2>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding-left: 15px; padding-right: 15px;">
                <table align="center" width="475" style="border-collapse: collapse;">
                    <tr style="padding: 0">
                        <td style="border: 1px solid #333; padding: 8px 10px">Description ({{ $payment_type }})</td>
                        <td style="border: 1px solid #333; padding: 8px 10px">Amount Paid</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            <table>
                                <tr>
                                    <td>Service</td>
                                    <td>: {{ $service->name ?? '' }}</td>

                                </tr>
                                <tr>
                                    <td>Date</td>
                                    <td>: {{ $booking->booking_date ?? '' }}</td>


                                </tr>
                                <tr>
                                    <td>Time</td>
                                    <td>: {{ $session->start_time ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td>Hours</td>
                                    <td>: {{ $session->duration ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Vendor</td>
                                    <td>: {{ $company->name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td>Address</td>
                                    <td>: {{ $company->address_line ?? '' }} {{ $company->postcode ?? '' }} {{ $company->city ?? '' }} {{ $company->state ?? '' }}</td>
                                </tr>
                            </table>
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">{{ $booking->price }}</td>
                    </tr>
                    @if ($booking->status == -1)
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            Booking Cancelled (Refunded 70%)
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">({{ $booking->refunded }})</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            Promotion Code : {{ $promotion->promo_code ?? '' }}
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">{{ -$promotion->percentage ?? '0.00' }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            Insurance
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">{{ $booking->insurance ?? '0.00' }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            Service Tax
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">{{ $booking->service_tax ?? '0.00' }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            Transportation Fees
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">{{ $booking->service_tax ?? '0.00' }}</td>
                    </tr>
                    <tr>
                        <td align="right" style="border: 1px solid #333; padding: 8px 10px">
                            <b>Total Amount Paid</b>
                        </td>
                        <td style="border: 1px solid #333; padding: 8px 10px">
                            @if ($booking->status == -1)
                                0.00
                            @else
                                {{ ($booking->price) - ($promotion->percentage) }}
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
