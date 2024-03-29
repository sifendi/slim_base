<?php
/**
* @project    ScientiaAPP - Web Apps Skeleton & CRUD Generator
* @package    App\Models
* @author     Benedict E. Pranata
* @copyright  (c) 2019 benedict.erwin@gmail.com
* @created    on Wed Oct 30 2019
* @license    GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.en.html>
*/

namespace App\Models;

class T_karyawan_hobby extends \App\Plugin\DataTablesMysql
{
	/* Declare private variable */
	private $Cacher;
	private $CacheExp;
	private $TagName;
	private $Sign;

	/* Constructor */
	public function __construct(\Slim\Container $container)
	{
		/* Call Parent Constructor */
		parent::__construct($container);

		/* Cache Setup */
		$this->Sign = $container->get('settings')['dbnya']['SIGNATURE'];
		$this->Cacher = $container->cacher;
		$this->TagName = hash('sha256', $this->Sign . 'T_karyawan_hobby');
		$this->CacheExp = 3600; # in seconds (1 hour)

		/* Table Setup */
		$this->setTable('t_karyawan_hobby')
			->setPkey('id_karyawan_hobby')
			->setSearchCols(['mk.nama_karyawan','mh.hobby'
		])
			->setDefaultOrder(['id_karyawan_hobby'=> 'DESC' ])
			->setQuery($this->alterSql());

	}

	public function alterSql()
    {
        return "SELECT mk.id_karyawan,
                       mk.nama_karyawan,
                       mh.id_hobby,
                       mh.hobby,
                       tk.id_karyawan_hobby
                FROM t_karyawan_hobby tk
                LEFT JOIN m_karyawan mk ON tk.id_karyawan=mk.id_karyawan
                LEFT JOIN m_hobby mh ON tk.id_hobby = mh.id_hobby";
    }

	/**
	 * Get Data in T_karyawan_hobby by Primary Key
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getByID(int $id)
	{
		try {
			$output = null;
			$cacheKey = hash('md5', $this->Sign . __METHOD__ . $id);
			$CachedString = $this->Cacher->getItem($cacheKey);
			if (!$CachedString->isHit()) {
				$output = $this->getDataById($id);
				$CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
				$this->Cacher->save($CachedString);
			}else {
				$output = $CachedString->get();
			}

			return $output;
		} catch (\Exception $e) {
			throw new \Exception($this->overrideSQLMsg($e->getMessage()));
		}
	}

	/**
	 * Retrieve data from T_karyawan_hobby
	 *
	 * @param array $data
	 * @return array $output
	 */
	public function read(array $data = [])
	{
		try {
			unset($data['draw']);
			$output = [];
			$cacheKey = hash('md5', $this->Sign . __METHOD__ . json_encode($data));
			$CachedString = $this->Cacher->getItem($cacheKey);
			if (!$CachedString->isHit()) {
				$output = [
					'datalist' => $this->get_datatables($data),
					'recordsTotal' => $this->count_all($data),
					'recordsFiltered' => $this->count_filtered($data)
				];
				$CachedString->set($output)->expiresAfter($this->CacheExp)->addTag($this->TagName);
				$this->Cacher->save($CachedString);
			} else {
				$output = $CachedString->get();
			}

			return $output;
		} catch (\Exception $e) {
			throw new \Exception($this->overrideSQLMsg($e->getMessage()));
		}
	}

	/**
	 * Insert Data in T_karyawan_hobby
	 *
	 * @param array $data
	 * @return int $last_insert_id
	 */
	public function create(array $data = [])
	{
		try {
			if($lastId = $this->saveData($data)){
				$this->Cacher->deleteItemsByTag($this->TagName);
				return $lastId;
			}else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception($this->overrideSQLMsg($e->getMessage()));
		}
	}

	/**
	 * Update data from T_karyawan_hobby
	 *
	 * @param array $data
	 * @param integer $id
	 * @return bool
	 */
	public function update(array $data = [], int $id)
	{
		try {
			$update = $this->updateData($data, [$this->getPkey() => $id]);
			$this->Cacher->deleteItemsByTag($this->TagName);
			return $update;
		} catch (\Exception $e) {
			throw new \Exception($this->overrideSQLMsg($e->getMessage()));
		}
	}

	/**
	 * Remove single or multiple data from T_karyawan_hobby
	 *
	 * @param array|integer $data
	 * @return bool
	 */
	public function delete($data)
	{
		try {
			$delete = $this->deleteData($data);
			$this->Cacher->deleteItemsByTag($this->TagName);
			return $delete;
		} catch (\Exception $e) {
			throw new \Exception($this->overrideSQLMsg($e->getMessage()));
		}
	}

}
