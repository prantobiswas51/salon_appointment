import React, { useState } from "react";

interface AppointmentData {
  client_id: number;
  service: string;
  appointment_time: string;
  duration?: number;
  attendance_status?: string;
  status?: string;
  reminder_sent?: boolean;
  notes?: string;
}

const AppointmentForm: React.FC = () => {
  const [data, setData] = useState<AppointmentData>({
    client_id: 1,
    service: "Hair Cut",
    appointment_time: "",
    duration: 60,
    attendance_status: "",
    status: "Scheduled",
    reminder_sent: false,
    notes: "",
  });

  const [processing, setProcessing] = useState<boolean>(false);

  // Handle changes in the input fields
  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setProcessing(true);

    try {
      const response = await fetch("/api/appointments", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      if (response.ok) {
        alert("Appointment successfully booked!");
      } else {
        const errorData = await response.json();
        alert(`Error: ${errorData.message}`);
      }
    } catch (error) {
      alert("An error occurred. Please try again.");
    } finally {
      setProcessing(false);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <div>
        <label htmlFor="service">Service</label>
        <select
          name="service"
          id="service"
          value={data.service}
          onChange={handleChange}
        >
          <option value="Hair Cut">Hair Cut</option>
          <option value="Beard Shaping">Beard Shaping</option>
          <option value="Other Services">Other Services</option>
        </select>
      </div>

      <div>
        <label htmlFor="appointment_time">Appointment Time</label>
        <input
          type="datetime-local"
          name="appointment_time"
          id="appointment_time"
          value={data.appointment_time}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="duration">Duration (minutes)</label>
        <input
          type="number"
          name="duration"
          id="duration"
          value={data.duration}
          onChange={handleChange}
        />
      </div>

      <div>
        <label htmlFor="notes">Notes</label>
        <textarea
          name="notes"
          id="notes"
          value={data.notes || ""} 
          onChange={handleChange}
        />
      </div>

      <div>
        <button type="submit" disabled={processing}>
          {processing ? "Booking..." : "Book Appointment"}
        </button>
      </div>
    </form>
  );
};

export default AppointmentForm;
