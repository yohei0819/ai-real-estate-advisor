<?php

namespace App\Services\Ai;

use App\Exceptions\AiException;

class PromptRegistry
{
    /** @var array<string, string> */
    private array $prompts = [
        'investment_report' => <<<'PROMPT'
あなたは不動産投資の専門アドバイザーです。以下の物件データを分析し、JSONのみで回答してください。

## 入力データ
- 物件価格: {property_price}円
- 月額賃料: {monthly_rent}円
- 稼働率: {occupancy_rate}
- 年間経費: {annual_expenses}円
- 借入金額: {loan_amount}円
- 金利: {interest_rate}%
- 借入年数: {loan_years}年
- 修繕積立率: {repair_reserve_rate}
- 表面利回り: {gross_yield}%
- 実質利回り（修繕費込み）: {net_yield}%
- 月次キャッシュフロー（修繕費・ローン返済前）: {monthly_cashflow}円

## 出力フィールド（必須）
- `riskScore`: 1〜10の整数（1=最低リスク、10=最高リスク）
- `summary`: 総合所見。入力データの数値を根拠に200字程度で記述する
- `recommendation`: 投資判断の推奨。「購入推奨」「慎重検討」「購入不推奨」のいずれかを冒頭に示し、理由を1〜2文で続ける
- `rationale`: 各スコア・判断の数値的根拠を箇条書きの配列で列挙する
- `risks`: 確認されたリスク項目を箇条書きの配列で列挙する
- `actionItems`: 推奨する具体的な対処アクションを箇条書きの配列で列挙する
- `disclaimer`: 本結果は参考情報であり投資判断の最終根拠にならない旨を記載する

JSONのみを返すこと。説明文・マークダウン・コードブロックは禁止。
PROMPT,
    ];

    public function get(string $name): string
    {
        if (! isset($this->prompts[$name])) {
            throw new AiException("Prompt [{$name}] is not registered.");
        }

        return $this->prompts[$name];
    }

    /**
     * プレースホルダーを実際の値で置換したプロンプトを返す
     *
     * @param  array<string, mixed>  $variables
     */
    public function build(string $name, array $variables): string
    {
        $prompt = $this->get($name);

        foreach ($variables as $key => $value) {
            $prompt = str_replace("{{$key}}", (string) $value, $prompt);
        }

        return $prompt;
    }
}
