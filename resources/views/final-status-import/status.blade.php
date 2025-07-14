@extends('layout.default')

@push('styles')
    <link rel="stylesheet" href="{{ Asset::get('css/ieducar.css') }}"/>
@endpush

@section('content')
    <table class="tablecadastro" width="100%" border="0" cellpadding="2" cellspacing="0" role="presentation">
        <tbody>
            <tr>
                <td class="formdktd" colspan="2" height="24">
                    <b>Resultado da Importação</b>
                </td>
            </tr>
            <tr>
                <td class="formmdtd" colspan="2">
                    <div style="margin: 10px 0;">
                        <strong>Status:</strong> {{ $result['status'] === 'completed' ? 'Concluída' : 'Falhou' }}<br>
                        <strong>Registros processados:</strong> {{ $result['processed'] ?? 0 }} de {{ $result['total'] ?? 0 }}
                    </div>
                </td>
            </tr>

            @if(isset($result['warnings']) && count($result['warnings']) > 0)
            <tr>
                <td class="formmdtd" colspan="2">
                    <div style="background-color: #fcf8e3; border: 1px solid #faebcc; color: #8a6d3b; padding: 10px; margin: 10px 0; border-radius: 4px;">
                        <h6 style="margin: 0 0 10px 0; font-weight: bold;">⚠️ Avisos ({{ count($result['warnings']) }}):</h6>
                        <div style="max-height: 200px; overflow-y: auto;">
                            @foreach($result['warnings'] as $warning)
                                <div style="margin: 5px 0; padding: 5px; background-color: rgba(255,255,255,0.5); border-radius: 3px;">
                                    <strong>Linha {{ $warning['row'] }}:</strong> {{ $warning['warning'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </td>
            </tr>
            @endif

            @if(isset($result['errors']) && count($result['errors']) > 0)
            <tr>
                <td class="formmdtd" colspan="2">
                    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0 32px 0; border-radius: 4px;">
                        <h6 style="margin: 0 0 10px 0; font-weight: bold;">❌ Erros ({{ count($result['errors']) }}):</h6>
                        <div style="max-height: 200px; overflow-y: auto;">
                            @foreach($result['errors'] as $error)
                                <div style="margin: 5px 0; padding: 5px; background-color: rgba(255,255,255,0.5); border-radius: 3px;">
                                    <strong>Linha {{ $error['row'] }}:</strong> {{ $error['error'] }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="separator"></div>

    <div style="text-align: center">
        @if($result['status'] === 'completed')
            <div style="background-color: #dff0d8; border: 1px solid #d6e9c6; color: #3c763d; padding: 15px; margin: 10px 0; border-radius: 4px;">
                <h5 style="margin: 0 0 10px 0;">✅ Importação Concluída!</h5>
                <p style="margin: 0;">
                    Total processado: <strong>{{ $result['processed'] ?? 0 }}</strong> registros<br>
                    @if(isset($result['ignored']) && $result['ignored'] > 0)
                        Ignorados: <strong>{{ $result['ignored'] }}</strong> (situação "Aprovado" no arquivo)<br>
                    @endif
                    @if(isset($result['warnings']) && count($result['warnings']) > 0)
                        Avisos: <strong>{{ count($result['warnings']) }}</strong><br>
                    @endif
                    @if(isset($result['errors']) && count($result['errors']) > 0)
                        Erros: <strong>{{ count($result['errors']) }}</strong>
                    @endif
                </p>
            </div>
        @else
            <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px;">
                <h5 style="margin: 0 0 10px 0;">❌ Importação Falhou!</h5>
                <p style="margin: 0;">Ocorreu um erro durante o processamento. Verifique os detalhes acima.</p>
            </div>
        @endif
        
        <div style="margin-top: 20px">
            <a href="{{ route('situacao-final-import.index') }}" class="btn-green">
                <i class="fa fa-refresh"></i> Nova Importação
            </a>
        </div>
    </div>
@endsection
 