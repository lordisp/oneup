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
                            <form action="{{route('signin')}}" method="post">
                                @csrf
                                <x-btn.primary type="submit" class="w-full  justify-center">SSO Sign in</x-btn.primary>
                            </form>
                        </div>
                    </div>

                    @if($errors->has('error_description'))
                        <div class="mb-6 border border-red-500 rounded px-4 py-2 bg-red-100 text-red-900 text-sm">
                            <div>{{$errors->get('error_description')[0]}}</div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>
</x-auth-layout>