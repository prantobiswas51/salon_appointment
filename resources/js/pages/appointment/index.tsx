import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { FilePen, Search, Trash, TimerReset } from 'lucide-react';

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
  start_time: string;
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

  const sendReminder = (id: number): void => {
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
        start_time: editing.start_time,
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

        {/* search form */}
        <form action="" method="get" className="mb-4 flex flex-col sm:flex-row">
          <input
            type="text"
            placeholder="Search by name, phone, email"
            className="border p-2 px-4 flex-1 rounded-lg mb-2 sm:mb-0 sm:mr-2"
            name="q"
          />
          <button
            type="submit"
            className="border flex justify-center items-center rounded-lg p-2 bg-pink-600 text-white"
          >
            <Search className="mr-1" /> <span>Search</span>
          </button>
        </form>

        {/* responsive table */}
        <div className="overflow-x-auto">
          <table className="hidden lg:table min-w-full border-gray-300 rounded-lg shadow-lg text-sm text-left">
            <thead className="text-gray-900 uppercase bg-pink-300">
              <tr>
                <th className="px-3 lg:px-6 py-3 border-b">Id</th>
                <th className="px-3 lg:px-6 py-3 border-b">Name</th>
                <th className="px-3 lg:px-6 py-3 border-b">Phone</th>
                <th className="px-3 lg:px-6 py-3 border-b">Service</th>
                <th className="px-3 lg:px-6 py-3 border-b">Time</th>
                <th className="px-3 lg:px-6 py-3 border-b">Duration</th>
                <th className="px-3 lg:px-6 py-3 border-b">Status</th>
                <th className="px-3 lg:px-6 py-3 border-b">Reminder</th>
                <th className="px-3 lg:px-6 py-3 border-b">Actions</th>
              </tr>
            </thead>
            <tbody>
              {appointments.data.map((appointment) => (
                <tr key={appointment.id} className="hover:bg-gray-50 transition">
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.id}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.client?.name}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.client?.phone}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.service}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.start_time}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.duration} Minutes</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.status}</td>
                  <td className="px-3 lg:px-6 py-4 border-b">{appointment.reminder_sent}</td>
                  <td className="px-3 lg:px-6 py-4 border-b flex items-center">

                    {/* <button
                      type="button"
                      className={`ml-3 ${deletingId === appointment.id
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:cursor-pointer'
                        }`}
                      onClick={() => sendReminder(appointment.id)}
                      disabled={deletingId === appointment.id}
                    >
                      <TimerReset
                      className="text-amber-500 hover:text-amber-600 hover:cursor-pointer"
                    />
                    </button> */}

                    <FilePen
                      className="text-amber-500 hover:text-amber-600 ml-2 hover:cursor-pointer"
                      onClick={() => openEdit(appointment)}
                    />

                    <button
                      type="button"
                      className={`ml-3 ${deletingId === appointment.id
                        ? 'opacity-50 cursor-not-allowed'
                        : 'hover:cursor-pointer'
                        }`}
                      onClick={() => deleteAppointment(appointment.id)}
                      disabled={deletingId === appointment.id}
                    >
                      <Trash className="text-red-500 hover:text-red-600" />
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Mobile Card View */}
          <div className="space-y-4 lg:hidden">
            {appointments.data.map((appointment) => (
              <div
                key={appointment.id}
                className="bg-white dark:bg-gray-800 border rounded-lg p-4 shadow-sm"
              >
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">ID</span>
                  <span className="font-medium">{appointment.id}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Name</span>
                  <span>{appointment.client?.name}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Phone</span>
                  <span>{appointment.client?.phone}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Service</span>
                  <span>{appointment.service}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Time</span>
                  <span>{appointment.start_time}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Status</span>
                  <span>{appointment.status}</span>
                </div>
                <div className="flex justify-between mb-2">
                  <span className="text-gray-600 text-sm">Reminder</span>
                  <span>{appointment.reminder_sent}</span>
                </div>

                <div className="flex justify-end mt-3">

                  <FilePen
                    className="text-amber-500 hover:text-amber-600 hover:cursor-pointer mr-3"
                    onClick={() => openEdit(appointment)}
                  />
                  <button
                    type="button"
                    className={`${deletingId === appointment.id
                      ? 'opacity-50 cursor-not-allowed'
                      : 'hover:cursor-pointer'
                      }`}
                    onClick={() => deleteAppointment(appointment.id)}
                    disabled={deletingId === appointment.id}
                  >
                    <Trash className="text-red-500 hover:text-red-600" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* pagination */}
        <div className="flex flex-wrap justify-center mt-6 space-x-2">
          {appointments.links.map((link, index) => (
            <button
              key={index}
              disabled={!link.url}
              dangerouslySetInnerHTML={{ __html: link.label }}
              onClick={() => link.url && router.visit(link.url)}
              className={`px-3 py-1 border rounded mb-2
            ${link.active ? "bg-pink-600 text-white" : "bg-white text-pink-600"}
            ${!link.url
                  ? "opacity-50 cursor-not-allowed"
                  : "hover:cursor-pointer"
                }`}
            />
          ))}
        </div>

        {/* edit modal */}
        {isModalOpen && editing && (
          <div className="fixed inset-0 bg-gray-700/20 flex justify-center items-center p-4">
            <div className="bg-sky-100 dark:bg-gray-800 dark:border-gray-100 w-full max-w-[95%] sm:max-w-[30rem] p-6 rounded-lg shadow-lg">
              <h2 className="text-xl font-bold mb-4">Edit Appointment</h2>
              <form onSubmit={handleUpdate}>
                <div className="mb-4">
                  <label className="block mb-2">Service</label>
                  <input
                    type="text"
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={editing.service}
                    onChange={(e) =>
                      setEditing({ ...editing, service: e.target.value })
                    }
                  />
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Duration</label>
                  <input
                    type="number"
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={editing.duration}
                    onChange={(e) =>
                      setEditing({ ...editing, duration: e.target.value })
                    }
                  />
                </div>

                {/* editing */}
                <div className="mb-4">
                  <label className="block mb-2">Appointment Start Time</label>
                  <input
                    type="datetime-local"
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={
                      editing.start_time
                        ? new Date(editing.start_time)
                          .toLocaleString("sv-SE", { timeZone: "Europe/Rome" })
                          .slice(0, 16) // "YYYY-MM-DDTHH:mm"
                        : ""
                    }
                    onChange={(e) =>
                      setEditing({
                        ...editing,
                        start_time: e.target.value,
                      })
                    }
                  />

                </div>

                <div className="mb-4">
                  <label className="block mb-2">Status</label>
                  <select
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={editing.status}
                    onChange={(e) =>
                      setEditing({ ...editing, status: e.target.value })
                    }
                  >
                    <option value="Confirmed">Confirmed</option>
                    <option value="Canceled">Canceled</option>
                    <option value="Scheduled">Scheduled</option>
                  </select>
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Attendance Status</label>
                  <select
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={editing.attendence_status}
                    onChange={(e) =>
                      setEditing({ ...editing, attendence_status: e.target.value })
                    }
                  >
                    <option value="Green">Green</option>
                    <option value="Red">Red</option>
                    <option value="Yellow">Yellow</option>
                  </select>
                </div>

                <div className="mb-4">
                  <label className="block mb-2">Notes</label>
                  <textarea
                    className="border p-2 rounded-lg w-full dark:border-gray-300"
                    value={editing.notes ?? ""}
                    onChange={(e) =>
                      setEditing({ ...editing, notes: e.target.value })
                    }
                  />
                </div>



                <div className="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                  <div>
                    <div className="font-medium text-gray-700 ">Client</div>
                    <div className="mt-1 ">{editing.client?.name}</div>
                  </div>
                  <div>
                    <div className="font-medium text-gray-700">Phone</div>
                    <div className="mt-1">{editing.client?.phone}</div>
                  </div>
                </div>

                <div className="flex justify-end flex-wrap">
                  <button
                    type="button"
                    className="mr-2 mb-2 border px-4 py-2 rounded-lg text-gray-600"
                    onClick={closeEdit}
                    disabled={submitting}
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    className="bg-blue-600 text-white px-4 py-2 rounded-lg disabled:opacity-60"
                    disabled={submitting}
                  >
                    {submitting ? "Saving…" : "Save Changes"}
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
