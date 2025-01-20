<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\CommonMail;
use App\Models\User;
use App\Traits\WebResponse;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Resources\Json\JsonResource;


class BaseService
{
    // use WebResponse;

    public function generateUserRegisteredToken(User $user): string
    {
        $timeNow = Carbon::now();
        return base64_encode($user->registered_email . '_time_' . $timeNow);
    }

    public function sendEmail(string $mailTo, string $subject, string $view, array $data = [], int $minutes = 0): void
    {
        $delay = now();
        if ($minutes) {
            $delay = $delay->addMinutes($minutes);
        }
        Log::info('########## STARTING SEND EMAIL');
        Log::info('###### Send Email: ' . $mailTo);
        Log::info('###### Subject: ' . $subject);
        Log::info('###### DATA: ', $data);
        dispatch(new SendEmailJob($mailTo, new CommonMail($subject, $view, $data)))->delay($delay);
        Log::info('########## END SEND EMAIL');
    }

    public function paginator(Collection $collection, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $collection->forPage(request()->input('page'), $perPage),
            $collection->count(),
            $perPage,
            request()->input('page'),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function responseData(
        string $status,
        array|Collection|JsonResource $data = []
    ) {
        return [
            'status' => $status,
            'data' => $data,
        ];
    }
}
