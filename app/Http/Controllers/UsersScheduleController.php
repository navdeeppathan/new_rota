namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersScheduleController extends Controller
{
    public function index()
    {
        $days = [
            '16th June', '17th June', '18th June', 
            '19th June', '20th June', '21st June', '22nd June'
        ];

        $schedule = [
            'T/Leaders' => [
                'AB' => ['-', '-', '-', '-', '-', 'LD', 'LD'],
            ],
            'Seniors' => [
                'AB' => ['LD', '-', '-', 'LD Meds', 'LD Meds', '-', '-'],
                'BA' => ['-', 'LD', 'LD', '-', 'LD', '-', '-'],
                'KK' => ['-', '-', '8-8', '-', '-', '8-8', '8-8'],
            ],
            'Carers' => [
                'JT' => ['-', '-', '6.45 LD', '-', 'LD', '-', 'LD'],
                'SF' => ['-', 'LD', '-', 'L', '-', '-', '-'],
                'MA' => ['LD', '-', '-', 'E', '-', '-', '-'],
                'OB' => ['-', '-', '-', '-', '-', '-', '-'],
                'UK' => ['-', '-', '-', '-', '-', '-', '-'],
                'TS' => ['LD', '-', '-', 'LD', '-', 'LD', '-'],
            ],
            'Bank' => [
                'AR' => ['-', 'LD', '-', 'LD', 'LD', '-', 'LD'],
                'BK' => ['-', '-', '-', '-', '-', '-', '-'],
                'AN' => ['-', '-', '-', '-', '-', '-', '-'],
            ],
        ];

        $totalIn = [4, 4, 4, 4, 4, 4, 4];

        return view('schedule.index', compact('days', 'schedule', 'totalIn'));
    }
}
