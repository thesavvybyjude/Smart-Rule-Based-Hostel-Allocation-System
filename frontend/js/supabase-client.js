// frontend/js/supabase-client.js
// We use the ES module build of Supabase
import { createClient } from 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js/+esm'

// Fetch configuration from our serverless endpoint
const response = await fetch('/api/config');
const config = await response.json();

export const supabase = createClient(config.url, config.anonKey);
