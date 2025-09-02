import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { FilePen, Search, Trash } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Appointment', href: '/appointment' },
];

type Client = {
  id: number;
  name: string;
  phone: string;
  email?: string;
};

type Appointment = {
  id: number;
  client_id: number;
  service: string;
  duration: string;
  attendence_status: string; // keep your current API field name
  appointment_time: string;
  status: string;
  notes: string;
  reminder_sent: string;
  client: Client;
};

type Paginated<T> = {
  data: T[];
  links: { url: string | null; label: string; active: boolean }[];
};

export default function Index() {
  const { appointments } = usePage<{ appointments: Paginated<Appointment> }>().props;

  // edit modal state
  const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
  const [editing, setEditing] = useState<Appointment | null>(null);
  const [submitting, setSubmitting] = useState<boolean>(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);

  const openEdit = (appt: Appointment): void => {
    // shallow copy so we can edit fields
    setEditing({ ...appt });
    setIsModalOpen(true);
  };

  const closeEdit = (): void => {
    setIsModalOpen(false);
    setEditing(null);
  };

  // ✅ Use Inertia for deletion and refresh only the `appointments` prop
  const deleteAppointment = (id: number): void => {
    if (!window.confirm('Are you sure you want to delete this appointment?')) return;

    setDeletingId(id);

    // If you have Ziggy: route('appointments.destroy', id)
    router.delete(`/appointment/delete/${id}`, {
      preserveScroll: true,
      onFinish: () => setDeletingId(null),
      onError: (errors) => {
        // eslint-disable-next-line no-console
        console.error(errors);
        window.alert('Failed to delete appointment.');
      },
      onSuccess: () => {
        // Reload just the appointments prop (works across Inertia versions)
        router.visit(window.location.href, { only: ['appointments'], preserveScroll: true });
      },
    });
  };

  const handleUpdate = (e: React.FormEvent<HTMLFormElement>): void => {
    e.preventDefault();
    if (!editing) return;

    setSubmitting(true);

    // if you have Ziggy's route() available, swap the url to: route('appointment.update', editing.id)
    const url = `/appointments/${editing.id}`;

    router.put(
      url,
      {
        service: editing.service,
        duration: editing.duration,
        attendence_status: editing.attendence_status,
        appointment_time: editing.appointment_time,
        status: editing.status,
        notes: editing.notes,
        // include client_id if your update endpoint expects it:
        client_id: editing.client_id,
      },
      {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => setSubmitting(false),
        onSuccess: closeEdit,
        onError: (errors) => {
          // eslint-disable-next-line no-console
          console.error(errors);
        },
      }
    );
  };

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Appointment" />
      <div className="p-2 m-4">
        <h1 className="text-2xl font-bold mb-4">Scheduled Appointments</h1>

        <form action="" method="get" className="mb-4 flex">
          <input
            type="text"
            placeholder="Search by name, phone, email"
            className="border p-2 px-4 min-w-lg rounded-md"
            name="q"
          />
          <button
            type="submit"
            className="border flex rounded-md ml-2 p-2 bg-pink-600 text-white"
          >
            <Search /> <span className="ml-1">Search</span>
          </button>
        </form>

        <table className="w-full border-gray-300 rounded-lg shadow-md text-sm text-left">
          <thead className="text-gray-900 uppercase rounded-md bg-pink-300">
            <tr>
              <th className="px-6 py-3 border-b p-2">Id</th>
              <th className="px-6 py-3 border-b">Name</th>
              <th className="px-6 py-3 border-b">Phone</th>
              <th className="px-6 py-3 border-b">Service</th>
              <th className="px-6 py-3 border-b">Time</th>
              <th className="px-6 py-3 border-b">Status</th>
              <th className="px-6 py-3 border-b">Reminder sent at</th>
              <th className="px-6 py-3 border-b">Actions</th>
            </tr>
          </thead>
          <tbody>
            {appointments.data.map((appointment) => (
              <tr key={appointment.id} className="hover:bg-gray-50 transition">
                <td className="px-6 py-4 border-b">{appointment.id}</td>
                <td className="px-6 py-4 border-b">{appointment.client?.name}</td>
                <td className="px-6 py-4 border-b">{appointment.client?.phone}</td>
                <td className="px-6 py-4 border-b">{appointment.service}</td>
                <td className="px-6 py-4 border-b">{appointment.appointment_time}</td>
                <td className="px-6 py-4 border-b">{appointment.status}</td>
                <td className="px-6 py-4 border-b">{appointment.reminder_sent}</td>
                <td className="px-6 py-4 border-b flex items-center">
                  <FilePen
                    className="text-amber-500 hover:text-amber-600 hover:cursor-pointer"
                    onClick={() => openEdit(appointment)}
                  />
                  <button
                    type="button"
                    aria-label="Delete appointment"
                    className={`ml-3 ${deletingId === appointment.id ? 'opacity-50 cursor-not-allowed' : 'hover:cursor-pointer'}`}
                    onClick={() => deleteAppointment(appointment.id)}
                    disabled={deletingId === appointment.id}
                    title={deletingId === appointment.id ? 'Deleting…' : 'Delete'}
                  >
                    <Trash className="text-red-500 hover:text-red-600" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>

        {/* pagination */}
        <div className="flex justify-center mt-6 space-x-2">
          {appointments.links.map((link, index) => (
            <button
              key={index}
              disabled={!link.url}
              // Note: label may contain HTML entities from Laravel pagination
              dangerouslySetInnerHTML={{ __html: link.label }}
              onClick={() => link.url && router.visit(link.url)}
              className={`px-3 py-1 border rounded
                ${link.active ? 'bg-pink-600 text-white' : 'bg-white text-pink-600'}
                ${!link.url ? 'opacity-50 cursor-not-allowed' : 'hover:cursor-pointer'}`}
            />
          ))}
        </div>

        {/* edit modal */}
        {isModalOpen && editing && (
          <div className="fixed inset-0 bg-gray-700/20 bg-opacity-50 flex justify-center items-center">
            <div className="bg-sky-100 min-w-xl p-6 rounded-md shadow-lg w-[30rem]">
              <h2 className="text-xl font-bold mb-4">Edit Appointment</h2>
              <form onSubmit={handleUpdate}>
                <div className="mb-4">
                  <label className="block mb-2">Service</label>
                  <input
                    type="text"
                    className="border p-2 rounded-md w-full"
                    value={editing.service}
                    onChange={(e) => setEditing({ ...editing, service: e.target.value })}
                  />
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Duration</label>
                  <input
                    type="text"
                    className="border p-2 rounded-md w-full"
                    value={editing.duration}
                    onChange={(e) => setEditing({ ...editing, duration: e.target.value })}
                  />
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Attendance Status</label>
                  <select
                    className="border p-2 rounded-md w-full"
                    value={editing.attendence_status}
                    onChange={(e) =>
                      setEditing({ ...editing, attendence_status: e.target.value })
                    }
                  >
                    <option value="Confirmed">Confirmed</option>
                    <option value="Canceled">Canceled</option>
                    <option value="Scheduled">Scheduled</option>
                    </select>
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Appointment Time</label>
                  <input
                    type="text"
                    className="border p-2 rounded-md w-full"
                    value={editing.appointment_time}
                    onChange={(e) =>
                      setEditing({ ...editing, appointment_time: e.target.value })
                    }
                  />
                  {/* if your API expects ISO datetime, consider using <input type="datetime-local" /> */}
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Status</label>
                  <input
                    type="text"
                    className="border p-2 rounded-md w-full"
                    value={editing.status}
                    onChange={(e) => setEditing({ ...editing, status: e.target.value })}
                  />
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Notes</label>
                  <textarea
                    className="border p-2 rounded-md w-full"
                    value={editing.notes ?? ''}
                    onChange={(e) => setEditing({ ...editing, notes: e.target.value })}
                  />
                </div>

                {/* read-only client info (optional) */}
                <div className="mb-4 grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <div className="font-medium text-gray-700">Client</div>
                    <div className="mt-1">{editing.client?.name}</div>
                  </div>
                  <div>
                    <div className="font-medium text-gray-700">Phone</div>
                    <div className="mt-1">{editing.client?.phone}</div>
                  </div>
                </div>

                <div className="flex justify-end">
                  <button
                    type="button"
                    className="mr-2 border px-4 py-2 rounded-md text-gray-600"
                    onClick={closeEdit}
                    disabled={submitting}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="bg-blue-600 text-white px-4 py-2 rounded-md disabled:opacity-60"
                    disabled={submitting}
                  >
                    {submitting ? 'Saving…' : 'Save Changes'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </AppLayout>
  );
}
