<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        Registrations
    </title>
    <style>
        body {
            font-size: 12px;
            font-family: Helvetica, sans-serif;
        }

        table {
            width: 100%;
        }

        .table td,
        .table th {
            padding: 2px 5px;
        }

        .table th {
            background: lightgray
        }

        .bg-grey {
            background: rgb(226, 226, 226)
        }

        .table tbody td.border,
        .border thead th,
        .border tbody td {
            border-bottom: 1px solid gray;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0px;
            right: 0px;
            height: 30px;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .no-border {
            border: none !important;
        }
    </style>
</head>

<body>
    <main>
        <table style="margin-bottom: 15px" cellspacing="0">
            <tbody>
                <tr>
                    <td>{{ $date }}</td>
                    <td class="text-right">£{{ round($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <table class="table border" cellspacing="0">
            <thead>
                <tr>
                    <th align="left">Day</th>
                    <th align="left">Start at</th>
                    <th align="left">End at</th>
                    <th align="left">Class Name</th>
                    <th align="left">Location</th>
                    <th align="left">NS</th>
                    <th align="left">AT (%)</th>
                    <th align="left">Capacity (%)</th>
                    <th align="left">Instructor</th>
                    <th align="left">Sign off</th>
                    <th align="left">Remote</th>
                    <th align="left">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registrations as $row)
                    @if ($row['is_total'])
                        <tr>
                            <td class="bg-grey q-td text-left" colspan="11">
                                Total
                            </td>
                            <td class="bg-grey q-td text-left" style="width: 40px;">
                                <span>£{{ round($row['cost'] ?? 0, 2) }}</span>
                            </td>
                        </tr>
                    @else
                        @php
                            $retval = 0;
                            $capacity = $row->capacity;
                            $space = $capacity - $row->total_bookings;
                            $standby = 5 - $row->total_stand_by_bookings;
                            if ($capacity) {
                                $retval = 100 - ($space / $capacity) * 100;
                            }
                            $calCapacity = "${capacity}(${space})(${standby}) \n" . round($retval, 2);
                        @endphp
                        <tr>
                            <td class="q-td text-left" style="width: 40px;">
                                {{ "{$row->day->value} - {$row->date_at_formated}" }}
                            </td>
                            <td class="q-td text-left" style="width: 10px;"><span>{{ $row->start_at_formated }}</span>
                            </td>
                            <td class="q-td text-left" style="width: 10px;"><span>{{ $row->end_at_formated }}</span>
                            </td>
                            <td class="q-td text-left" style="width: 40px;">
                                <span>{{ $row->class ? $row->class->name : '' }}</span>
                            </td>
                            <td class="q-td text-left" style="width: 60px;">
                                <span>{{ $row->location ? $row->location->label : '' }}</span>
                            </td>
                            <td class="q-td " style="width: 10px;">
                                <span>{{ $row->has_sign_off ? $row->no_show : 0 }}</span>
                            </td>
                            <td class="q-td text-center" style="width: 10px;">
                                <span class="">
                                    {{ round((($row->total_bookings - $row->no_show) * 100) / $row->capacity, 2) }}
                                </span>
                            </td>
                            <td class="q-td text-center" style="width: 40px;"><span>{{ $calCapacity }}</span></td>
                            <td class="q-td text-left" style="width: 40px;">
                                <span>{{ $row->instructor ? $row->instructor->name : '' }}</span>
                            </td>
                            <td class="q-td text-left" style="width: 10px;">{{ $row->has_sign_off ? 'Yes' : 'No' }}
                            </td>
                            <td class="q-td text-center" style="width: 10px;">
                                <span>{{ $row->has_remote ? 'Yes' : 'No' }}</span>
                            </td>
                            <td class="q-td text-left" style="width: 10px;">
                                <span>£{{ round($row->cost ?? 0, 2) }}</span>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </main>
</body>

</html>
