export async function downloadReportFile(url: string, fallbackFilename: string): Promise<void> {
  const response = await fetch(url, {
    credentials: 'same-origin',
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const blob = await response.blob();
  const disposition = response.headers.get('Content-Disposition') || '';
  const filenameMatch = disposition.match(/filename="?([^"]+)"?/i);
  const filename = filenameMatch?.[1] || fallbackFilename;

  const blobUrl = window.URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = blobUrl;
  anchor.download = filename;
  document.body.appendChild(anchor);
  anchor.click();
  anchor.remove();
  window.URL.revokeObjectURL(blobUrl);
}
