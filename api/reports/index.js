const { supabaseAdmin } = require('../utils/supabaseAdmin');

module.exports = async (req, res) => {
  if (req.method === 'GET') {
    try {
      const { data: students, error: err1 } = await supabaseAdmin.from('students').select('*');
      if (err1) throw err1;

      const { data: rooms, error: err2 } = await supabaseAdmin.from('rooms').select('*');
      if (err2) throw err2;

      const totalStudents = students.length;
      const allocated = students.filter(s => s.allocation_status === 'allocated').length;
      const waitlisted = students.filter(s => s.allocation_status === 'waitlisted').length;
      const pending = students.filter(s => s.allocation_status === 'pending').length;
      
      const totalRooms = rooms.length;
      const totalCapacity = rooms.reduce((acc, r) => acc + r.capacity, 0);
      const totalOccupied = rooms.reduce((acc, r) => acc + r.occupied_slots, 0);

      return res.status(200).json({
        students: { total: totalStudents, allocated, waitlisted, pending },
        rooms: { total: totalRooms, capacity: totalCapacity, occupied: totalOccupied }
      });
    } catch (error) {
      return res.status(500).json({ error: error.message });
    }
  }

  res.status(405).json({ message: 'Method not allowed' });
};
