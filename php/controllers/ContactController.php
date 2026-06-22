<?php
/** HTTP layer for contacts (port of src/controllers/contact.controller.js). Thin. */
class ContactController
{
    private ContactService $service;

    public function __construct()
    {
        $this->service = new ContactService();
    }

    // POST /api/contacts
    public function create(Request $req): void
    {
        $body = $req->body;
        Validator::assert(Validator::createContact($body));
        $contact = $this->service->create($body);
        send_response(['statusCode' => 201, 'message' => 'Contact created', 'data' => $contact]);
    }

    // GET /api/contacts
    public function getAll(Request $req): void
    {
        $result = $this->service->findAll($req->query);
        send_response(['message' => 'Contacts fetched', 'data' => $result['items'], 'meta' => $result['meta']]);
    }

    // GET /api/contacts/search?q=
    public function search(Request $req): void
    {
        $query = $req->query;
        Validator::assert(Validator::search($query));
        $q = $query['q'] ?? '';
        $items = $this->service->search($q);
        send_response([
            'message' => 'Search results',
            'data'    => $items,
            'meta'    => ['total' => count($items), 'q' => $q],
        ]);
    }

    // GET /api/contacts/:id
    public function getById(Request $req): void
    {
        Validator::assert(Validator::idParam($req->params['id']));
        $contact = $this->service->findById((int) $req->params['id']);
        send_response(['message' => 'Contact fetched', 'data' => $contact]);
    }

    // PUT /api/contacts/:id
    public function update(Request $req): void
    {
        Validator::assert(Validator::idParam($req->params['id']));
        $body = $req->body;
        Validator::assert(Validator::updateContact($body));
        $contact = $this->service->update((int) $req->params['id'], $body);
        send_response(['message' => 'Contact updated', 'data' => $contact]);
    }

    // DELETE /api/contacts/:id
    public function remove(Request $req): void
    {
        Validator::assert(Validator::idParam($req->params['id']));
        $this->service->remove((int) $req->params['id']);
        send_response(['message' => 'Contact deleted', 'data' => ['id' => (int) $req->params['id']]]);
    }
}
