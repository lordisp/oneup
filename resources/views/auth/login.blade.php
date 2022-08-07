<x-auth-layout>
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <img class="mx-auto h-12 w-auto" src="/images/logos/oneup_logo.png"
                 alt="Workflow">
            <h2 class="mt-6 text-center">Sign in to your account</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="mb-6">
                    <div class="mb-6 ">
                        <div>
                            <x-btn.primary class="w-full  justify-center">SSO Sign in</x-btn.primary>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>
                </div>

                <x-accordion>
                    <x-accordion.body id="0" request="1" title="Development" class="text-sm">
                        <form class="space-y-6" action="{{route('login')}}" method="POST">
                            @csrf
                            <x-input.group for="email" inline label="Email address" :error="$errors->first('email')">
                                <x-input.text name="email" type="email" autocomplete="email" required
                                              class="w-full"></x-input.text>
                            </x-input.group>

                            <x-input.group for="password" inline label="Password" :error="$errors->first('password')">
                                <x-input.text id="password" name="password" type="password" autocomplete="current-password"
                                              required class="w-full"></x-input.text>
                            </x-input.group>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">

                                    <input id="remember-me" name="remember-me" type="checkbox"
                                           class="h-4 w-4 border-gray-300 focus:ring-lhg-yellow h-4 rounded text-lhg-yellow w-4 transition duration-150 ease-in-out">
                                    <label for="remember-me" class="ml-2 block text-sm text-gray-900"> Remember me </label>
                                </div>

                                <div class="text-sm">
                                    <a href="{{route('password.request')}}"
                                       class="font-medium text-lhg-blue hover:underline"> Forgot your
                                        password? </a>
                                </div>
                            </div>

                            <div>
                                <x-btn.primary type="submit" class="w-full justify-center">Sign in</x-btn.primary>
                            </div>
                        </form>
                    </x-accordion.body>
                </x-accordion>


            </div>
        </div>
    </div>
</x-auth-layout>