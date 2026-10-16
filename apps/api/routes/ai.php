<?php

use App\Mcp\Servers\EditorialServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', EditorialServer::class)->middleware('auth:sanctum');
