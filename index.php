<?php
/**
 * Fallback: redirect to public/ if accessed from root directly.
 * For proper setup, point your document root to the public/ folder.
 */
header('Location: public/');
exit;
