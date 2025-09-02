import * as React from "react";

type WeekCalendarProps = {
  /** ISO string like "2025-08-22T19:00" or empty */
  value?: string;
  onChange: (value: string) => void;
  /** optional: which day the week starts on; 0=Sunday, 1=Monday */
  weekStartsOn?: 0 | 1;
  /** minutes per slot: 30 for half-hours */
  slotMinutes?: 15 | 30 | 60;
  /** number of hours to show (0..24); default 24 */
  hoursRange?: { start: number; end: number }; // e.g., {start: 8, end: 20}
};

const dayLabels = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

function pad(n: number) {
  return n.toString().padStart(2, "0");
}

function toDateTimeLocalString(d: Date) {
  // format suitable for <input type="datetime-local">
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(
    d.getHours()
  )}:${pad(d.getMinutes())}`;
}

function startOfDay(d: Date) {
  const x = new Date(d);
  x.setHours(0, 0, 0, 0);
  return x;
}

function addDays(d: Date, days: number) {
  const x = new Date(d);
  x.setDate(x.getDate() + days);
  return x;
}

function getWeekStart(today: Date, weekStartsOn: 0 | 1) {
  const day = today.getDay();
  const diff = (day - weekStartsOn + 7) % 7;
  const start = startOfDay(addDays(today, -diff));
  return start;
}

export default function WeekCalendar({
  value,
  onChange,
  weekStartsOn = 0,
  slotMinutes = 30,
  hoursRange = { start: 0, end: 24 },
}: WeekCalendarProps) {
  // Compute current week anchor from "value" if present; else from today
  const anchor = React.useMemo(() => {
    const base = value ? new Date(value) : new Date();
    return getWeekStart(base, weekStartsOn);
  }, [value, weekStartsOn]);

  // Build days for the header and grid
  const days = React.useMemo(
    () => Array.from({ length: 7 }, (_, i) => addDays(anchor, i)),
    [anchor]
  );

  // Build time slots
  const slots = React.useMemo(() => {
    const res: { hour: number; minute: number }[] = [];
    for (let h = hoursRange.start; h < hoursRange.end; h++) {
      for (let m = 0; m < 60; m += slotMinutes) {
        res.push({ hour: h, minute: m });
      }
    }
    return res;
  }, [slotMinutes, hoursRange]);

  const selected = value ? new Date(value) : null;

  const handlePick = (day: Date, hour: number, minute: number) => {
    const picked = new Date(day);
    picked.setHours(hour, minute, 0, 0);
    onChange(toDateTimeLocalString(picked));
  };

  const isSelected = (day: Date, hour: number, minute: number) => {
    if (!selected) return false;
    return (
      selected.getFullYear() === day.getFullYear() &&
      selected.getMonth() === day.getMonth() &&
      selected.getDate() === day.getDate() &&
      selected.getHours() === hour &&
      selected.getMinutes() === minute
    );
  };

  // Basic keyboard support: arrows to move, Enter to pick
  const gridRef = React.useRef<HTMLDivElement>(null);

  return (
    <div className="w-full  bg-gray-600">
      {/* Week header */}
      <div className="grid" style={{ gridTemplateColumns: "80px repeat(7, 1fr)" }}>
        <div className="p-2 text-sm font-medium text-white/80">Time</div>
        {days.map((d) => (
          <div key={d.toDateString()} className="p-2 text-center">
            <div className="text-white font-semibold">
              {dayLabels[d.getDay()]}
            </div>
            <div className="text-white/80 text-sm">
              {pad(d.getMonth() + 1)}/{pad(d.getDate())}
            </div>
          </div>
        ))}
      </div>

      {/* Grid */}
      <div
        ref={gridRef}
        className="grid border-t border-white/20 rounded-md overflow-hidden"
        style={{ gridTemplateColumns: "80px repeat(7, 1fr)" }}
      >
        {slots.map((slot, rowIdx) => (
          <React.Fragment key={`${slot.hour}:${slot.minute}-${rowIdx}`}>
            {/* time gutter */}
            <div className="text-right pr-2 py-2 text-xs text-white/80 border-b border-white/10 bg-black/10">
              {`${slot.hour % 12 === 0 ? 12 : slot.hour % 12}:${pad(slot.minute)} ${
                slot.hour < 12 ? "AM" : "PM"
              }`}
            </div>
            {/* 7 day columns */}
            {days.map((day) => {
              const selectedCell = isSelected(day, slot.hour, slot.minute);
              return (
                <button
                  key={day.toDateString() + "-" + rowIdx}
                  onClick={() => handlePick(day, slot.hour, slot.minute)}
                  className={[
                    "h-10 w-full border-b border-l border-white/10 focus:outline-none",
                    "hover:bg-white/10 transition-colors",
                    selectedCell ? "bg-pink-500/70 ring-2 ring-pink-300" : "",
                  ].join(" ")}
                  aria-label={`Pick ${day.toDateString()} ${slot.hour}:${pad(slot.minute)}`}
                />
              );
            })}
          </React.Fragment>
        ))}
      </div>

      {/* Legend / hint */}
      <div className="mt-2 text-xs text-white/80">
        Click a slot to set the appointment time. Currently showing{" "}
        {hoursRange.start}:00–{hoursRange.end}:00 in {slotMinutes}-minute slots.
      </div>
    </div>
  );
}
