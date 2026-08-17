export function formatDate(isoString) {
  if (!isoString) return '';
  const d = new Date(isoString);
  if (isNaN(d.getTime())) return isoString;
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(d);
}

export function formatTime(isoString) {
  if (!isoString) return '';
  // Handles both full ISO strings and 'HH:mm:ss' strings
  let d = new Date(isoString);
  if (isNaN(d.getTime())) {
    d = new Date(`1970-01-01T${isoString}`);
  }
  if (isNaN(d.getTime())) return isoString;
  
  return new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(d) + ' WIB';
}
