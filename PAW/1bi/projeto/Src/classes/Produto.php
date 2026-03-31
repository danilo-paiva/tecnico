<?php
/*sistema deve possuir uma classe Produto com os atributos nome, 
preço e quantidade em estoque. Devem ser criados métodos para adicionar itens ao estoque, 
remover itens do estoque e calcular o valor total do estoque.
 O sistema deve mostrar no console ou na página as alterações realizadas no estoque.. 
 O formulário da aplicação deve permitir o envio de dados de 5 produtos, que deverão ser processados utilizando a classe Produto.
  Dica, criar um vetor de objetos Produtos */
  // me permita concluir rapaz (sem valor semântico, meme antigo)
  // status : finalizado(necessario questionar atributo alteracao!)

declare(strict_types=1); 
namespace Src\classes;
class Produto
{
    private string $nome = "vazio";
    private float $preco=0.0;
    private int $quantidade=0;
    
    public function __construct() {}

    public function setNome(string $novoNome) : void
    {
        
        if ($novoNome == '') 
            {
                
                throw new \InvalidArgumentException(
                'Nome do produto não pode ser vazio.');
            }
        $novoNome = strip_tags($novoNome); 
        
        $this->nome = $novoNome;
    }

    public function setPreco(float $novoPreco) : void 
    {
        if ($novoPreco <=0 )
            {
                // 00000000000000 NOVA PROMOÇÃO: compre 1 e leve tudo de graça ou ainda ganhe $
                //                                                                \/
                
                throw new \InvalidArgumentException(
                'O valor do produto não pode ser vazio ou negativo.');
            }
            
            
        
        $this->preco = $novoPreco;
    }

    public function addEstoque(int $quantidadeAdcionanda) : void
    {
        if ($quantidadeAdcionanda < 0) 
            {
                
                throw new \InvalidArgumentException(
                'Quantidade adicionada não pode ser negativa.');
            }
                                                    
        
        $this->quantidade += $quantidadeAdcionanda;
    }

    public function subEstoque(int $quantidadeSubtraida) : void
    {
        if ($quantidadeSubtraida < 0) 
            {
                // 0000000000000 ooooooooooooo cara (sem valor semântico)
                
                throw new \InvalidArgumentException(
                'Quantidade removida não pode ser negativa.');
            }

        if ($this->quantidade - $quantidadeSubtraida < 0) 
            {
                // 0000 tão achando que eh licitação essa bosta kkkkkkkkk
                
                throw new \InvalidArgumentException(
                'Quantidade total do estoque não pode ser negativa.');
            }
       
    
        
        $this->quantidade -= $quantidadeSubtraida;
    }

    public function getNome() : string
    {
        return $this->nome;
    }

    public function getPreco() : float
    {
        return $this->preco;
    }

    public function getEstoque() : float
    {
        return $this->quantidade;
    }

    public function valorEstoque() : float 
    {
        return ($this->preco * $this->quantidade);
    }

    
}