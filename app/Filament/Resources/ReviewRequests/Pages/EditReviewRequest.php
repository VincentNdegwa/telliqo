<?php

namespace App\Filament\Resources\ReviewRequests\Pages;

use App\Filament\Resources\ReviewRequests\ReviewRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditReviewRequest extends EditRecord
{
    protected static string $resource = ReviewRequestResource::class;
}
