<?php

namespace Database\Factories;

use App\Models\UserAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    protected $model = UserAddress::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $addresses = [
            ["北京市", "市辖区", "东城区"],
            ["北京市", "市辖区", "朝阳区"],
            ["河北省", "石家庄市", "长安区"],
            ["上海市", "市辖区", "浦东新区"],
            ["上海市", "市辖区", "静安区"],
            ["浙江省", "杭州市", "滨江区"],
            ["浙江省", "杭州市", "钱塘区"],
            ["广东省", "广州市", "天河区"],
            ["广东省", "广州市", "花都区"],
            ["福建省", "福州市", "鼓楼区"],
            ["江西省", "南昌市", "东湖区"],
            ["江西省", "赣州市", "章贡区"],
            ["湖南省", "长沙市", "天心区"],
            ["湖北省", "武汉市", "江岸区"],
            ["重庆市", "市辖区", "渝中区"],
            ["四川省", "成都市", "青羊区"],
            ["贵州省", "贵阳市", "南明区"],
            ["云南省", "昆明市", "五华区"],
            ["陕西省", "西安市", "未央区"],
            ["甘肃省", "兰州市", "城关区"],
            ["青海省", "西宁市", "城中区"],
            ["宁夏回族自治区", "银川市", "兴庆区"],
            ["新疆维吾尔自治区", "乌鲁木齐市", "天山区"],
            ["西藏自治区", "拉萨市", "城关区"],
        ];

        $address = $this->faker->randomElement($addresses);

        return [
            'province' => $address[0],
            'city' => $address[1],
            'district' => $address[2],
            'address' => sprintf('第%d街道第%d号', $this->faker->randomNumber(2), $this->faker->randomNumber(3)),
            'zip' => $this->faker->postcode,
            'contact_name' => $this->faker->name,
            'contact_phone' => $this->faker->phoneNumber,
        ];
    }
}
