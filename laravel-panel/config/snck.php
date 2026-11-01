<?php

return [
    // Set only during the one-time import, then remove it from the target .env.
    // This is the legacy Node app's NODE_ENCRYPTION_KEY, not Laravel APP_KEY.
    'legacy_node_encryption_key' => env('LEGACY_NODE_ENCRYPTION_KEY'),
];
