const { supabaseAdmin } = require('../utils/supabaseAdmin');

module.exports = async (req, res) => {
  if (req.method === 'GET') {
    const { data, error } = await supabaseAdmin.from('rooms').select('*, hostels(name)');
    if (error) return res.status(500).json({ error: error.message });
    return res.status(200).json(data);
  }

  if (req.method === 'POST') {
    const { hostel_id, room_number, capacity } = req.body;
    const { data, error } = await supabaseAdmin.from('rooms').insert([{ hostel_id, room_number, capacity, occupied_slots: 0 }]);
    if (error) return res.status(500).json({ error: error.message });
    return res.status(201).json(data);
  }

  res.status(405).json({ message: 'Method not allowed' });
};
