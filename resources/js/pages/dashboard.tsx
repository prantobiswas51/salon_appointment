import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Timer, CalendarDays, RefreshCcw } from 'lucide-react';

import { Dialog } from "@headlessui/react";
import FullCalendar from "@fullcalendar/react";
import timeGridPlugin from "@fullcalendar/timegrid";
import { router, Head, useForm, usePage } from "@inertiajs/react";
import interactionPlugin from "@fullcalendar/interaction";

import { useEffect, useState } from "react";

type Appointment = {
    id: number;
    client_name: string;
    service: string;
    start_time: string;
    duration: number;
    status: string;
};

type CalendarEvent = {
    title: string;
    start: string;
    end: string;
    backgroundColor: string;
    borderColor: string;
};

type Props = {
    appointments: Appointment[];
    inProgress: Appointment | null;
    nextAppointment: Appointment | null;
    countToday: number;
    countWeek: number;
    countMonth: number;
    calendarEvents: CalendarEvent[]; // 👈 new
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

export default function Dashboard({
    appointments,
    inProgress,
    nextAppointment,
    countToday,
    countWeek,
    countMonth,
    calendarEvents, // 👈 new
}: Props) {
    const [events, setEvents] = useState<CalendarEvent[]>(calendarEvents); // 👈 init with backend events
    const [isOpen, setIsOpen] = useState(false);
    const [activeTab, setActiveTab] = useState<"existing" | "new">("new");

    const handleSync = () => {
        router.get(route("sync"));
    };

    const page = usePage();
    const flash = (page.props as any)?.flash || {};

    const { data, setData, post, processing, errors, reset } = useForm({
        client_number: "",
        email: "",
        new_client_name: "",
        new_client_phone: "",
        service: "",
        start_time: "",
        duration: "",
        status: "Scheduled",
        notes: "",
    });

    const formatDateForInput = (date: Date) => {
        return date
            .toLocaleString("sv-SE", { timeZone: "Europe/Rome" })
            .replace(" ", "T")
            .slice(0, 16);
    };

    // ---- When Slot is Selected ----
    const handleSelect = (info: any) => {
        setData("start_time", formatDateForInput(info.start));
        setIsOpen(true);
    };

    // ---- Submit Form ----
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post("/appointment/store", {
            onSuccess: () => {
                alert("Appointment saved!");
                setIsOpen(false);
                reset();
                // optional: re-sync calendar after save
                // setEvents([...events, newEventFromBackend])
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            {/* Current & Next Appointment */}
            <div className="flex flex-col md:grid md:grid-cols-2 dark:bg-gray-900 p-2 rounded-lg">
                {inProgress && (
                    <div className="w-auto border p-2 bg-sky-200/30 border-sky-500 m-2 rounded-md shadow-sm">
                        <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Appointment in Progress</h2>
                        <h3 className='font-semibold flex'>{inProgress.client_name}</h3>
                        <h3>{inProgress.service}</h3>
                        <div className="flex justify-between items-center">
                            <h3>Time : {inProgress.start_time}</h3>
                            <span className='bg-green-200 px-2 p-1 rounded-2xl text-sm'>In progress</span>
                        </div>
                    </div>
                )}

                {nextAppointment && (
                    <div className="w-auto border p-2 bg-amber-200/30 border-amber-500 m-2 rounded-md shadow-sm">
                        <h2 className='text-xl font-bold flex'><Timer className='mr-2' />Next Appointment</h2>
                        <h3 className='font-semibold flex'>{nextAppointment.client_name}</h3>
                        <h3>{nextAppointment.service}</h3>
                        <div className="flex justify-between items-center">
                            <h3>Time : {nextAppointment.start_time}</h3>
                            <span className=' px-2 p-1 rounded-2xl text-sm'>Next</span>
                        </div>
                    </div>
                )}
            </div>

            {/* Stats */}
            <h2 className='text-xl font-bold flex mx-2 mt-6'>Today's Agenda</h2>
            <div className="w-auto px-4 md:grid md:grid-cols-3 gap-3 dark:bg-gray-900 p-2 rounded-lg">
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Today</div>
                    <span className='text-3xl flex'>{countToday} <div className="text-sm ml-2">appointments</div></span>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Week</div>
                    <span className='text-3xl flex'>{countWeek} <div className="text-sm ml-2">appointments</div></span>
                </div>
                <div className="p-2 my-2 rounded-md bg-pink-300/60 border border-pink-500 shadow-sm">
                    <div className="font-bold">Month</div>
                    <span className='text-3xl flex'>{countMonth} <div className="text-sm ml-2">appointments</div></span>
                </div>
            </div>

            {/* List of Appointments */}
            <h2 className='text-xl font-bold mx-2 mt-6 flex'><CalendarDays className='mr-2' />Today's Agenda</h2>
            <div className="w-auto mx-2 rounded-md">
                {appointments.map((app) => (
                    <div
                        key={app.id}
                        className="p-2 my-2 rounded-md border-green-600 border shadow-sm md:flex lg:max-w-[50%] md:justify-between md:items-center"
                    >
                        <div className="flex justify-between">
                            <div className="font-bold text-gray-600 flex items-center">
                                <Timer className='mr-1' />
                                {app.start_time}
                            </div>
                            <span
                                className={`p-2 py-1 rounded-2xl md:ml-4 ${app.status === 'completed'
                                    ? 'bg-green-600 text-white'
                                    : app.status === 'in_progress'
                                        ? 'bg-blue-400/60 text-black'
                                        : 'bg-pink-400/60 text-black'
                                    }`}
                            >
                                {app.status}
                            </span>
                        </div>
                        <div className="font-bold ml-2">{app.client_name}</div>
                        <div className="ml-2">{app.service}</div>
                    </div>
                ))}
            </div>

            {/* Calendar */}
            <div className="p-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-bold mb-4">Calendar</h2>
                    <button
                        onClick={handleSync}
                        className="px-3 py-1 bg-blue-500 flex items-center text-white rounded hover:bg-blue-600"
                    >
                        <RefreshCcw className="mr-2 w-4" /> Sync
                    </button>
                </div>

                <FullCalendar
                    plugins={[timeGridPlugin, interactionPlugin]}
                    initialView="timeGridWeek"
                    selectable={true}
                    selectMirror={true}
                    selectOverlap={false}
                    events={events} // 👈 from backend
                    select={handleSelect}
                    slotMinTime="00:00:00"
                    slotMaxTime="24:00:00"
                    height="auto"
                    selectLongPressDelay={200}
                    slotLabelFormat={{
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: false,
                    }}
                    eventTimeFormat={{
                        hour: "2-digit",
                        minute: "2-digit",
                        hour12: false,
                    }}
                />
            </div>

            {/* Popup Modal with Form + Tabs */}
            <Dialog open={isOpen} onClose={() => setIsOpen(false)} className="relative z-50 p-6 dialog">

                <div className="fixed inset-0 z-[9999] bg-gray-700/20 flex justify-center items-center p-4">
                    <div className="bg-sky-100 dark:bg-gray-800 dark:border-gray-100 w-full max-w-[95%] sm:max-w-[30rem] rounded-lg shadow-lg 
                  max-h-[90vh] overflow-y-auto p-6">
                        <h2 className="text-xl font-bold mb-4">Edit Appointment</h2>

                        {/* Tabs */}
                        <div className="flex space-x-4 mb-6">
                            <button
                                type="button"
                                onClick={() => {
                                    setActiveTab("new");
                                    setData({
                                        client_number: "",
                                        email: "",
                                        new_client_name: "",
                                        new_client_phone: "",
                                        service: "",
                                        duration: "",
                                        start_time: data.start_time,
                                        status: "Scheduled",
                                        notes: "",
                                    });
                                }}
                                className={`px-4 py-2 rounded ${activeTab === "new"
                                    ? "bg-pink-600 text-white"
                                    : "bg-gray-200 text-gray-700"
                                    }`}
                            >
                                New Client
                            </button>
                            <button
                                type="button"
                                onClick={() => {
                                    setActiveTab("existing");
                                    setData({
                                        client_number: "",
                                        email: "",
                                        new_client_name: "",
                                        new_client_phone: "",
                                        service: "",
                                        duration: "",
                                        start_time: data.start_time,
                                        status: "Scheduled",
                                        notes: "",
                                    });
                                }}
                                className={`px-4 py-2 rounded ${activeTab === "existing"
                                    ? "bg-pink-600 text-white"
                                    : "bg-gray-200 text-gray-700"
                                    }`}
                            >
                                Existing Client
                            </button>
                        </div>

                        {/* FORM */}
                        <form onSubmit={submit} className="space-y-4">
                            {activeTab === "existing" ? (
                                <div>
                                    <label className="block mb-1">Client Number</label>
                                    <input
                                        type="text"
                                        placeholder="e.g. +1 848 648 8448"
                                        className="w-full border p-2 rounded"
                                        value={data.client_number}
                                        onChange={(e) => setData("client_number", e.target.value)}
                                    />
                                    {errors.client_number && (
                                        <p className="text-red-500">{errors.client_number}</p>
                                    )}
                                </div>
                            ) : (
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block mb-1">Client Name</label>
                                        <input
                                            type="text"
                                            className="w-full border p-2 rounded"
                                            placeholder="e.g. John"
                                            value={data.new_client_name}
                                            onChange={(e) => setData("new_client_name", e.target.value)}
                                        />
                                        {errors.new_client_name && (
                                            <p className="text-red-500">{errors.new_client_name}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="block mb-1">Client Phone</label>
                                        <input
                                            type="tel"
                                            className="w-full border p-2 rounded"
                                            placeholder="e.g. +2 485 485 744"
                                            value={data.new_client_phone}
                                            onChange={(e) => setData("new_client_phone", e.target.value)}
                                        />
                                        {errors.new_client_phone && (
                                            <p className="text-red-500">{errors.new_client_phone}</p>
                                        )}
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block mb-1">Email</label>
                                        <input
                                            type="email"
                                            className="w-full border p-2 rounded"
                                            placeholder="clientmail@domain.com"
                                            value={data.email}
                                            onChange={(e) => setData("email", e.target.value)}
                                        />
                                        {errors.email && <p className="text-red-500">{errors.email}</p>}
                                    </div>
                                </div>
                            )}

                            <div className="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                {/* Service */}
                                <div>
                                    <label className="block mb-1">Service</label>
                                    <select
                                        value={data.service}
                                        onChange={(e) => setData("service", e.target.value)}
                                        className="w-full border p-2 rounded dark:bg-gray-900"
                                    >
                                        <option value="">Select</option>
                                        <option value="Hair Cut">Hair Cut</option>
                                        <option value="Beard Shaping">Beard Shaping</option>
                                        <option value="Other Services">Other Services</option>
                                    </select>
                                    {errors.service && <p className="text-red-500">{errors.service}</p>}
                                </div>
                                {/* Duration */}
                                <div>
                                    <label className="block mb-1">Duration (Minutes)</label>
                                    <input
                                        type="number"
                                        className="w-full border p-2 rounded"
                                        placeholder="30"
                                        value={data.duration}
                                        onChange={(e) => setData("duration", e.target.value)}
                                    />
                                    {errors.duration && <p className="text-red-500">{errors.duration}</p>}
                                </div>
                            </div>

                            {/* Appointment Time */}
                            <div>
                                <label className="block mb-1">Appointment Time</label>
                                <input
                                    type="datetime-local"
                                    className="w-full border p-2 rounded"
                                    value={data.start_time}
                                    onChange={(e) => setData("start_time", e.target.value)}
                                />
                                {errors.start_time && (
                                    <p className="text-red-500">{errors.start_time}</p>
                                )}
                            </div>



                            {/* Status */}
                            <div>
                                <label className="block mb-1">Status</label>
                                <select
                                    value={data.status}
                                    onChange={(e) => setData("status", e.target.value)}
                                    className="w-full border p-2 rounded dark:bg-gray-900"
                                >
                                    <option value="Scheduled">Scheduled</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Canceled">Canceled</option>
                                </select>
                                {errors.status && <p className="text-red-500">{errors.status}</p>}
                            </div>

                            {/* Notes */}
                            <div>
                                <label className="block mb-1">Notes</label>
                                <textarea
                                    className="w-full border p-2 rounded"
                                    value={data.notes}
                                    onChange={(e) => setData("notes", e.target.value)}
                                />
                            </div>

                            {/* Buttons */}
                            <div className="flex justify-end gap-2">
                                <button
                                    type="button"
                                    className="px-4 py-2 bg-gray-300 rounded hover:cursor-pointer dark:text-gray-900"
                                    onClick={() => setIsOpen(false)}
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-4 py-2 bg-pink-600 text-white rounded hover:cursor-pointer"
                                >
                                    Save Appointment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Dialog>
        </AppLayout>
    );
}
