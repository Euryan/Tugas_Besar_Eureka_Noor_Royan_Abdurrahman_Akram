<?php require_once __DIR__ . '/auth.php'; require_admin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Form Tailwind Grid 2-8-2</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 antialiased">
  
  <?php include 'navbar.php'; ?>
    <<!-- Membuat main sebagai 12 grid cols -->
  <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 grid grid-cols-12 gap-4">
    
    <form class="col-span-12 md:col-span-8 md:col-start-3 bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 rounded-xl">
      <div class="space-y-12">
        
        <div class="border-b border-gray-900/10 pb-12">
          <h1 class="text-2xl font-extrabold text-gray-900">Profile</h1>
          <p class="mt-1 text-sm text-gray-600">This information will be displayed publicly so be careful what you share.</p>
          <br>
          

         <div class="border-b border-gray-900/10 pb-12">
          <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
          <p class="mt-1 text-sm text-gray-600">Use a permanent address where you can receive mail.</p>

          <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-3">
              <label for="first-name" class="block text-sm/6 font-medium text-gray-900">First name</label>
              <div class="mt-2">
                <input id="first-name" type="text" name="first-name" autocomplete="given-name" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="last-name" class="block text-sm/6 font-medium text-gray-900">Last name</label>
              <div class="mt-2">
                <input id="last-name" type="text" name="last-name" autocomplete="family-name" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-4">
              <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
              <div class="mt-2">
                <input id="email" type="email" name="email" autocomplete="email" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-3">
              <label for="country" class="block text-sm/6 font-medium text-gray-900">Country</label>
              <div class="mt-2 grid grid-cols-1">
                <select id="country" name="country" autocomplete="country-name" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-gray-50 py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                  <option>United States</option>
                  <option>Canada</option>
                  <option>Mexico</option>
                </select>
                <svg viewBox="0 0 16 16" fill="currentColor" data-slot="icon" aria-hidden="true" class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4">
                  <path d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" fill-rule="evenodd" />
                </svg>
              </div>
            </div>

            <div class="col-span-full">
              <label for="street-address" class="block text-sm/6 font-medium text-gray-900">Street address</label>
              <div class="mt-2">
                <input id="street-address" type="text" name="street-address" autocomplete="street-address" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-2 sm:col-start-1">
              <label for="city" class="block text-sm/6 font-medium text-gray-900">City</label>
              <div class="mt-2">
                <input id="city" type="text" name="city" autocomplete="address-level2" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-2">
              <label for="region" class="block text-sm/6 font-medium text-gray-900">State / Province</label>
              <div class="mt-2">
                <input id="region" type="text" name="region" autocomplete="address-level1" class="block w-full rounded-md bg-gray-50 px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>

            <div class="sm:col-span-2">
              <label for="postal-code" class="block text-sm/6 font-medium text-gray-900">ZIP / Postal code</label>
              <div class="mt-2">
                <input id="postal-code" type="text" name="postal-code" autocomplete="postal-code" class="block w-full rounded-md bg-gray-50  px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
              </div>
            </div>
          </div>
        </div>
        </div>
      <div class="mt-6 flex items-center justify-end gap-x-6">
        <button type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</button>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
      </div>
    </form>
  </main>

</body>
</html>