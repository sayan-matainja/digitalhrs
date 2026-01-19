<?php

namespace App\Helpers\SMPush;

class SMPushPayload
{
    /* Private */
    protected string $title = "";
    protected string $message = "";
    protected array $data = [];
    protected array $recipients = [];
    protected bool $isSilence = false;
    protected string $androidChannelID = "digital_hr_channel";
    protected string $channelID = "digital_hr_channel";

    /* Public */
    public array $payload = [];


    public function __construct(string $title,
                                string $message,
                                array $data,
                                array $recipients,
                                bool $isSilence
    )
    {
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->recipients = $recipients;
        $this->isSilence = $isSilence;

        $this->payload['data'] = $this->data;

        if ($this->isAndroid) {
            $this->payload['priority'] = "high";
            $this->payload['android_channel_id'] = $this->androidChannelID;
            $this->payload['channel_id'] = $this->channelID;
            $this->payload['sound'] = 'beep.wav';

            if ($this->isSilence) {
                $this->payload['data'] += [
                    'title' => $this->title,
                    'message' => $this->message
                ];
            } else {
                $this->payload['notification'] = [
                    'title' => $this->title,
                    'body' => $this->message,
                    'sound' => 'beep.wav',
                ];
            }
        } else {
            $this->payload['notification'] = [
                'title' => $this->title,
                'body' => $this->message,
                'sound' => 'beep.wav',
            ];
        }

        $this->setRecipients($this->recipients);
    }

    public function getCurlPayload(): false|string
    {
        $this->payload['registration_ids'] = array_unique($this->payload['registration_ids']);
        sort($this->payload['registration_ids']);

        if (isset($this->payload['time_to_live']) && !isset($this->payload['collapse_key'])) {
            $this->payload['collapse_key'] = 'GCM Notifications';
        }

        return json_encode($this->payload);
    }

    public function addRecipient(string $registrationId): void
    {
        $this->payload['registration_ids'][] = $registrationId;
    }

    public function setRecipients(array $registrationIds): void
    {
        $this->payload['registration_ids'] = $registrationIds;
    }

    public function clearRecipients(): void
    {
        $this->payload['registration_ids'] = [];
    }
}
