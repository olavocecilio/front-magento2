# Teste Front-End Magento para a Híbrido
O teste foi criado para avaliar as habilidades como Front-End Magento 2.

## O teste
Para executar as tarefas solicitadas foi criado um novo tema e 2 módulos.
Inicialmente foi baixada e instalada em cada módulo a biblioteca owl.carousel para criar o Slider e o carrossel de produtos.
Posteriormente foi criado um novo tema com as alterações novas solicitadas à partir do tema Luma.

## Instruções
- Primeiramente deverá ser importado no banco de dados o dump chamado "dump_front_test_hibrido.sql" que encontra-se na raiz do repositório.
- Após a conexão do banco de dados com a instalação Magento, siga para os próximos passos.
- Acesse o Painel CMS e navegue até Content > Design > Configuration e selecione o novo tema de nome "Olavo Theme".
- Com o tema ativado as áreas "Como funciona" e "Conheça nossos diferenciais" já devem ser exibidas.
- Para ativar o Slider principal rode o seguinte comando no terminal: bin/magento module:enable Olavo_BannerSlider .
- Para ativar o Carrosel de produtos rode o seguinte comando no terminal: bin/magento module:enable Olavo_ProductSlider .

Dessa mandeira todas as áreas solicitadas já deve estar sendo exibidas!

Para alterar os banners da grade deve ser acessado no Painel CMS o caminho Content > Elements > Blocks e após isso podemos selecionar qualquer um dos banner que queremos alterar bem como o link para o qual apontaremos.
