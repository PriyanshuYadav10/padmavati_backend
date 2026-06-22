<?php
/** HTTP layer for settings (port of src/controllers/settings.controller.js). */
class SettingsController
{
    private SettingsService $service;

    public function __construct()
    {
        $this->service = new SettingsService();
    }

    // GET /api/settings
    public function get(Request $req): void
    {
        $settings = $this->service->get();
        send_response(['message' => 'Settings fetched', 'data' => $settings]);
    }

    // POST /api/settings
    public function save(Request $req): void
    {
        $body = $req->body;
        Validator::assert(Validator::saveSettings($body));
        $settings = $this->service->save($body);
        send_response(['message' => 'Settings saved', 'data' => $settings]);
    }
}
