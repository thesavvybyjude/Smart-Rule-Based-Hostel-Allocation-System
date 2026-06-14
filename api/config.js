require('dotenv').config();

module.exports = (req, res) => {
  // Only expose the public anon key and URL. 
  // NEVER expose the SERVICE_ROLE_KEY here.
  res.status(200).json({
    url: process.env.SUPABASE_URL,
    anonKey: process.env.SUPABASE_ANON_KEY
  });
};
