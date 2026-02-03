import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(relativeTime);

export function formatChatTime(utcTime) {
  if (!utcTime) return '';
  
  const local = dayjs.utc(utcTime).local();
  const now = dayjs();
  
  // If today, show time only
  if (local.isSame(now, 'day')) {
    return local.format('h:mm A'); // "2:00 PM"
  }
  
  // If yesterday
  if (local.isSame(now.subtract(1, 'day'), 'day')) {
    return 'Yesterday';
  }
  
  // If within a week, show day name
  if (local.isAfter(now.subtract(7, 'day'))) {
    return local.format('ddd'); // "Mon", "Tue"
  }
  
  // Otherwise show date
  return local.format('MMM D'); // "Jan 15"
}

export function formatFullDateTime(utcTime) {
  return dayjs.utc(utcTime).local().format('MMM D, YYYY h:mm A');
}

export function getRelativeTime(utcTime) {
  return dayjs.utc(utcTime).local().fromNow(); // "2 hours ago"
}