const { supabaseAdmin } = require('../utils/supabaseAdmin');

module.exports = async (req, res) => {
  if (req.method === 'GET') {
    const { data, error } = await supabaseAdmin.from('hostels').select('*');
    if (error) return res.status(500).json({ error: error.message });
    return res.status(200).json(data);
  }
  
  if (req.method === 'POST') {
    const { name, gender_category, total_rooms } = req.body;
    const { data, error } = await supabaseAdmin.from('hostels').insert([{ name, gender_category, total_rooms }]);
    if (error) return res.status(500).json({ error: error.message });
    return res.status(201).json(data);
  }

  res.status(405).json({ message: 'Method not allowed' });
};
