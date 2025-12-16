@extends('layout.public')

@section('content')
    <style>
        #login-form {
            max-width: 100% !important;
            width: 100% !important;
        }
        #main {
            max-width: 100% !important;
        }
    </style>
    <div style="max-width: 1200px; margin: 0 auto; padding: 30px; text-align: left; background: white; border-radius: 8px;">
        <h1 style="color: #333; margin-bottom: 30px; font-size: 28px; border-bottom: 2px solid #e0e0e0; padding-bottom: 15px;">POLÍTICA DE PRIVACIDADE – I-DIÁRIO</h1>

        <div style="line-height: 1.8; color: #555; font-size: 15px;">
            <p style="margin-bottom: 20px;">
                Esta Política de Privacidade descreve como o aplicativo i-Diário coleta, utiliza, armazena, compartilha e protege os dados pessoais de seus usuários, em conformidade com a Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018 – LGPD) e com as diretrizes da Google Play Store.
            </p>
            <p style="margin-bottom: 20px;">
                Ao utilizar o i-Diário, o usuário declara estar ciente e de acordo com as práticas descritas nesta Política.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">1. Coleta de Dados</h2>
            <p style="margin-bottom: 20px;">
                O i-Diário poderá coletar os seguintes dados, de acordo com a finalidade do aplicativo:
            </p>

            <h3 style="color: #333; margin-top: 20px; margin-bottom: 10px; font-size: 18px;">1.1 Dados fornecidos pelo usuário</h3>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Nome completo;</li>
                <li style="margin-bottom: 8px;">E-mail institucional;</li>
                <li style="margin-bottom: 8px;">Identificação funcional ou acadêmica (ex.: matrícula);</li>
                <li style="margin-bottom: 8px;">Dados de autenticação (login e senha);</li>
                <li style="margin-bottom: 8px;">Informações pedagógicas e administrativas inseridas no uso do sistema.</li>
            </ul>

            <h3 style="color: #333; margin-top: 20px; margin-bottom: 10px; font-size: 18px;">1.2 Dados coletados automaticamente</h3>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Informações técnicas do dispositivo (modelo, sistema operacional, versão do app);</li>
                <li style="margin-bottom: 8px;">Endereço IP e registros de acesso;</li>
                <li style="margin-bottom: 8px;">Logs de uso e desempenho do aplicativo.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                A coleta limita-se ao mínimo necessário para o funcionamento adequado do i-Diário, respeitando os princípios da necessidade, finalidade e adequação.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">2. Armazenamento e Transferência de Dados</h2>
            <p style="margin-bottom: 20px;">
                Os dados coletados são:
            </p>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Armazenados em servidores seguros, próprios ou de terceiros contratados;</li>
                <li style="margin-bottom: 8px;">Tratados exclusivamente para finalidades institucionais, pedagógicas e administrativas;</li>
                <li style="margin-bottom: 8px;">Protegidos por mecanismos de controle de acesso e autenticação.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                A transferência de dados poderá ocorrer:
            </p>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Entre sistemas institucionais integrados ao i-Diário;</li>
                <li style="margin-bottom: 8px;">Para provedores de serviços de infraestrutura tecnológica, quando estritamente necessário;</li>
                <li style="margin-bottom: 8px;">Mediante obrigação legal, regulatória ou ordem judicial.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                Não há comercialização ou compartilhamento indevido de dados pessoais com terceiros.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">3. Segurança</h2>
            <p style="margin-bottom: 20px;">
                O i-Diário adota medidas técnicas e administrativas adequadas para proteger os dados pessoais, incluindo:
            </p>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Criptografia de dados em trânsito e, quando aplicável, em repouso;</li>
                <li style="margin-bottom: 8px;">Controle de acesso baseado em perfis de usuário;</li>
                <li style="margin-bottom: 8px;">Monitoramento de acessos e registros de auditoria;</li>
                <li style="margin-bottom: 8px;">Atualizações periódicas de segurança.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                Apesar das medidas adotadas, nenhum sistema é totalmente imune a riscos. Em caso de incidente de segurança que possa acarretar risco ou dano relevante aos titulares, serão adotadas as providências legais cabíveis.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">4. Configuração de Cookies e Tecnologias Semelhantes</h2>
            <p style="margin-bottom: 20px;">
                O i-Diário pode utilizar cookies ou tecnologias similares para:
            </p>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Manter sessões autenticadas;</li>
                <li style="margin-bottom: 8px;">Melhorar a experiência do usuário;</li>
                <li style="margin-bottom: 8px;">Garantir o funcionamento correto das funcionalidades.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                Esses mecanismos não são utilizados para fins publicitários ou de rastreamento comercial. O usuário pode, quando aplicável, gerenciar permissões diretamente nas configurações do dispositivo.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">5. Direitos dos Titulares de Dados</h2>
            <p style="margin-bottom: 20px;">
                Nos termos da LGPD, o usuário possui os seguintes direitos:
            </p>
            <ul style="margin-bottom: 20px; padding-left: 30px;">
                <li style="margin-bottom: 8px;">Confirmação da existência de tratamento de dados;</li>
                <li style="margin-bottom: 8px;">Acesso aos dados pessoais;</li>
                <li style="margin-bottom: 8px;">Correção de dados incompletos, inexatos ou desatualizados;</li>
                <li style="margin-bottom: 8px;">Anonimização, bloqueio ou eliminação de dados desnecessários;</li>
                <li style="margin-bottom: 8px;">Portabilidade dos dados, quando aplicável;</li>
                <li style="margin-bottom: 8px;">Eliminação dos dados tratados com consentimento;</li>
                <li style="margin-bottom: 8px;">Informação sobre compartilhamento de dados;</li>
                <li style="margin-bottom: 8px;">Revogação do consentimento, quando este for a base legal.</li>
            </ul>
            <p style="margin-bottom: 20px;">
                As solicitações poderão ser realizadas por meio dos canais institucionais responsáveis pela gestão do i-Diário.
            </p>

            <h2 style="color: #333; margin-top: 30px; margin-bottom: 15px; font-size: 20px;">6. Modificação da Política de Privacidade e Proteção de Dados</h2>
            <p style="margin-bottom: 20px;">
                Esta Política de Privacidade poderá ser atualizada a qualquer tempo, visando adequação legal, técnica ou operacional.
            </p>
            <p style="margin-bottom: 20px;">
                As alterações relevantes serão comunicadas aos usuários por meio do próprio aplicativo ou por canais institucionais oficiais. A continuidade do uso do i-Diário após as atualizações implica ciência e concordância com os novos termos.
            </p>
            <p style="margin-bottom: 20px; margin-top: 30px; font-weight: bold;">
                Última atualização: 16/12/2025
            </p>
        </div>
    </div>
@endsection
