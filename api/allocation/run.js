const { supabaseAdmin } = require('../utils/supabaseAdmin');

module.exports = async (req, res) => {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method Not Allowed' });
  }

  try {
    // 1. Reset allocations
    await supabaseAdmin.from('allocations').delete().neq('id', '00000000-0000-0000-0000-000000000000'); // Delete all
    await supabaseAdmin.rpc('reset_room_slots'); // We'll do it manually if RPC isn't available
    
    // Instead of RPC, we can just fetch all rooms and set occupied_slots to 0
    const { data: allRooms } = await supabaseAdmin.from('rooms').select('id');
    if (allRooms && allRooms.length > 0) {
      await supabaseAdmin.from('rooms')
        .update({ occupied_slots: 0 })
        .in('id', allRooms.map(r => r.id));
    }

    // Update students to pending (only those who requested, i.e., not 'not_requested')
    const { data: requestedStudents } = await supabaseAdmin.from('students')
        .select('id')
        .neq('allocation_status', 'not_requested');
    
    if (requestedStudents && requestedStudents.length > 0) {
        await supabaseAdmin.from('students')
            .update({ allocation_status: 'pending' })
            .in('id', requestedStudents.map(s => s.id));
    }

    // 2. Fetch data
    const { data: students, error: err1 } = await supabaseAdmin
      .from('students')
      .select('*')
      .eq('allocation_status', 'pending');
      
    const { data: rooms, error: err2 } = await supabaseAdmin
      .from('rooms')
      .select('*, hostels(gender_category)');

    if (err1 || err2) throw new Error('Database fetch error');

    // 3. Assign priority scores
    const scoredStudents = students.map(student => {
      let score = 0;
      if (student.medical_status) score += 10;
      if (student.level === 400) score += 5;
      else if (student.level === 300) score += 3;
      else if (student.level === 200) score += 2;
      else if (student.level === 100) score += 1;
      
      return { ...student, priority_score: score };
    });

    // 4. Sort students
    scoredStudents.sort((a, b) => b.priority_score - a.priority_score);

    let allocatedCount = 0;
    let waitlistedCount = 0;

    // 5. Allocate
    for (const student of scoredStudents) {
      // Find suitable room
      const suitableRoom = rooms.find(room => 
        room.hostels.gender_category === student.gender && 
        room.occupied_slots < room.capacity
      );

      if (suitableRoom) {
        // Allocate
        await supabaseAdmin.from('allocations').insert({
          student_id: student.id,
          room_id: suitableRoom.id
        });
        
        suitableRoom.occupied_slots += 1; // Update local state
        await supabaseAdmin.from('rooms')
          .update({ occupied_slots: suitableRoom.occupied_slots })
          .eq('id', suitableRoom.id);
          
        await supabaseAdmin.from('students')
          .update({ allocation_status: 'allocated' })
          .eq('id', student.id);
          
        allocatedCount++;
      } else {
        // Waitlist
        await supabaseAdmin.from('students')
          .update({ allocation_status: 'waitlisted' })
          .eq('id', student.id);
          
        waitlistedCount++;
      }
    }

    return res.status(200).json({ 
      success: true, 
      allocated: allocatedCount, 
      waitlisted: waitlistedCount 
    });

  } catch (error) {
    console.error('Allocation Error:', error);
    return res.status(500).json({ message: error.message });
  }
};
