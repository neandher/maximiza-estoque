<?php

namespace App\Event;

class FlashBagEvents
{
    const MESSAGE_TYPE_SUCCESS = 'success';
    const MESSAGE_TYPE_ERROR = 'danger';

    const MESSAGE_SUCCESS_INSERTED = 'resource.flashbag.success.inserted';
    const MESSAGE_ERROR_INSERTED = 'resource.flashbag.error.inserted';

    const MESSAGE_SUCCESS_UPDATED = 'resource.flashbag.success.updated';
    const MESSAGE_ERROR_UPDATED = 'resource.flashbag.error.updated';

    const MESSAGE_SUCCESS_DELETED = 'resource.flashbag.success.deleted';
    const MESSAGE_ERROR_DELETED = 'resource.flashbag.error.deleted';
}