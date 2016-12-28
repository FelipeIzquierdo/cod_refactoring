<?php


/**
 * Class Service
 */
class Service extends  Model
{

    /**
     * @var array
     */
    protected $fillable = ['driver_id', 'status_id', 'car_id'];

    /**
     * @param $data
     */
    public function update ($data)
    {
        $this->fill($data);
        $this->save();
    }
}

/**
 * Class Driver
 */
class Driver extends  Model
{

    /**
     * @var array
     */
    protected $fillable = ['available', 'car_id'];

    /**
     * @param $data
     */
    public function update ($data)
    {
        $this->fill($data);
        $this->save();
    }
}

/**
 * Class User
 */
class User extends  Model
{

    /**
     * @return bool
     */
    public function isIphone()
    {
        return $this->type == 1;
    }
}


/**
 * Class Push
 */
class Push extends  Model
{

    /**
     * @param Service $service
     * @param $pushMessage
     */
    public function operatingSystem(Service $service, $pushMessage)
    {
        if ($service->user->isIphone()) {
            $this->ios($service->user->uuid, $pushMessage, 1, 'honk.wav', 'Open', array('serviceId'  => $service->id));
        } else {
            $this->android2($service->user->uuid, $pushMessage, 1, 'default', 'Open', array('serviceId'  => $service->id));
        }
    }
}


/**
 * @param Request $request
 * @param Service $service
 * @param Driver $driver
 * @return mixed
 */
function postConfirm(Request $request, Service $service, Driver $driver)
{
    $this->validate($request, [
        'service_id'    => 'required|integer|exists:services',
        'driver_id'     => 'required|integer|exists:drivers'
    ]);
    $service->find($request->get('service_id'));
    $driver->find($request->get('driver_id'));

    if ($service->status_id == '6') {
        return Response::json(array('error' => 2));
    }
    if (is_null($service->driver_id) && $service->status_id == 1) {
        $service->update( array(
            'driver_id' => $driver->id,
            'status_id' => 2,
            'car_id'    => $driver->car_id
        ));
        $driver->update(array(
            'available' => 0
        ));
        if ($service->user->uuid == '') {
            return Response::json(array('error' => 0));
        }
        Push::make()->operatingSystem($service, 'Tu service ha sido confirmado!');
        return Response::json(array('error' => 0));
    }
    return Response::json(array('error' => 1));
}