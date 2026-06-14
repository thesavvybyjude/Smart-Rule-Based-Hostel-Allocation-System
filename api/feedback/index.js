const { supabaseAdmin } = require('../utils/supabaseAdmin');

module.exports = async (req, res) => {
  if (req.method === 'GET') {
    const { data, error } = await supabaseAdmin.from('feedback').select('*, students(fullname, email)');
    if (error) return res.status(500).json({ error: error.message });
    return res.status(200).json(data);
  }

  if (req.method === 'POST') {
    const { student_id, subject, message, type } = req.body;
    const { data, error } = await supabaseAdmin.from('feedback').insert([{ student_id, subject, message, type, status: 'pending' }]);
    if (error) return res.status(500).json({ error: error.message });
    return res.status(201).json(data);
  }

  res.status(405).json({ message: 'Method not allowed' });
};
