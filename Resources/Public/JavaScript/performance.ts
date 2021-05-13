import {getCLS, getFCP, getFID, getLCP, getTTFB} from 'https://unpkg.com/web-vitals?module';
import uuidv4 from 'https://unpkg.com/uuid/dist/esm-browser/v4.js?module';

const requestUuid = uuidv4();
let counter = 0;

async function send(endPoint: string, data: any) {
  const bodyString = JSON.stringify(data);
  // Use `navigator.sendBeacon()` if available, falling back to `fetch()`.
  // if (navigator.sendBeacon && navigator.sendBeacon(endPoint, bodyString)) {
  //   return true;
  // }
  return fetch(endPoint, {
    body: bodyString,
    mode: 'cors',
    method: 'POST',
    keepalive: true,
  });
}

async function sendToAnalytics(metric: any) {
  const url = new URL(window.location.href);
  url.searchParams.set('webvitalstracker', '1');
  counter++;
  await send(url.toString(), {
    name: metric.name,
    value: metric.value,
    requestUuid,
    counter,
  });
}

getCLS(sendToAnalytics, true);
getFCP(sendToAnalytics, true);
getFID(sendToAnalytics, true);
getLCP(sendToAnalytics, true);
getTTFB(sendToAnalytics);
